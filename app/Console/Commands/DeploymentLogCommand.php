<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Deployment;
use App\Services\DeploymentLogger;
use Illuminate\Console\Command;
use Throwable;

class DeploymentLogCommand extends Command
{
    protected $signature = 'gigranker:deployment
        {action : start|success|fail|list}
        {--id= : Deployment UUID}
        {--environment=production : Deployment environment}
        {--version= : Release version}
        {--commit= : Git commit SHA}
        {--triggered-by= : Deployment actor or automation name}
        {--source= : Deployment source}
        {--message= : Completion or failure message}';

    protected $description = 'Create, complete, fail, or inspect deployment history records';

    public function handle(DeploymentLogger $logger): int
    {
        $action = (string) $this->argument('action');

        return match ($action) {
            'start' => $this->start($logger),
            'success' => $this->finalizeDeployment($logger, true),
            'fail' => $this->finalizeDeployment($logger, false),
            'list' => $this->listDeployments(),
            default => $this->invalidAction($action),
        };
    }

    private function start(DeploymentLogger $logger): int
    {
        $deployment = $logger->start(
            environment: (string) $this->option('environment'),
            version: $this->option('version') !== null ? (string) $this->option('version') : null,
            commitSha: $this->option('commit') !== null ? (string) $this->option('commit') : null,
            triggeredBy: $this->option('triggered-by') !== null ? (string) $this->option('triggered-by') : null,
            source: $this->option('source') !== null ? (string) $this->option('source') : null,
        );

        $this->info('Deployment started: '.$deployment->deployment_id);
        return self::SUCCESS;
    }

    private function finalizeDeployment(DeploymentLogger $logger, bool $success): int
    {
        $id = $this->option('id');
        if (! is_string($id) || $id === '') {
            $this->error('--id is required for success/fail.');
            return self::INVALID;
        }

        $deployment = Deployment::query()->where('deployment_id', $id)->first();
        if ($deployment === null) {
            $this->error('Deployment not found.');
            return self::FAILURE;
        }

        try {
            $message = $this->option('message');
            $message = is_string($message) ? $message : null;

            $deployment = $success
                ? $logger->succeed($deployment, $message)
                : $logger->fail($deployment, $message);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }

        $this->info(sprintf(
            'Deployment %s: %s (%sms)',
            $deployment->deployment_id,
            $deployment->status,
            (string) ($deployment->duration_ms ?? 0),
        ));

        return $success ? self::SUCCESS : self::FAILURE;
    }

    private function listDeployments(): int
    {
        $deployments = Deployment::query()
            ->latest('id')
            ->limit(20)
            ->get(['deployment_id', 'environment', 'version', 'commit_sha', 'status', 'started_at', 'finished_at']);

        if ($deployments->isEmpty()) {
            $this->line('No deployment history found.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Environment', 'Version', 'Commit', 'Status', 'Started', 'Finished'],
            $deployments->map(fn (Deployment $deployment): array => [
                $deployment->deployment_id,
                $deployment->environment,
                $deployment->version ?? '-',
                $deployment->commit_sha ? substr($deployment->commit_sha, 0, 12) : '-',
                $deployment->status,
                (string) $deployment->started_at,
                (string) ($deployment->finished_at ?? '-'),
            ])->all(),
        );

        return self::SUCCESS;
    }

    private function invalidAction(string $action): int
    {
        $this->error("Unknown deployment action '{$action}'. Use start, success, fail, or list.");
        return self::INVALID;
    }
}
