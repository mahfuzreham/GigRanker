<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Deployment;
use App\Services\DeploymentRollbackService;
use Illuminate\Console\Command;
use Throwable;

class DeploymentRollbackCommand extends Command
{
    protected $signature = 'gigranker:rollback
        {deployment : Successful deployment UUID to restore}
        {--execute : Actually reset the current Git working tree to the target commit}
        {--yes : Confirm execution without an interactive prompt}';

    protected $description = 'Validate or execute a rollback to a successful deployment';

    public function handle(DeploymentRollbackService $rollbackService): int
    {
        $target = Deployment::query()
            ->where('deployment_id', (string) $this->argument('deployment'))
            ->first();

        if ($target === null) {
            $this->error('Deployment not found.');
            return self::FAILURE;
        }

        if ($target->status !== 'success') {
            $this->error('Rollback target must have success status.');
            return self::FAILURE;
        }

        $execute = (bool) $this->option('execute');
        if ($execute && ! $this->option('yes')) {
            $this->error('Rollback changes the Git working tree. Add --yes together with --execute.');
            return self::INVALID;
        }

        try {
            $rollback = $rollbackService->rollback($target, $execute);
        } catch (Throwable $exception) {
            $this->error('Rollback failed: '.$exception->getMessage());
            return self::FAILURE;
        }

        $this->info($execute
            ? 'Rollback completed: '.$rollback->deployment_id
            : 'Rollback validated: '.$rollback->deployment_id);
        $this->line('Target commit: '.$target->commit_sha);
        $this->line('Status: '.$rollback->status);
        $this->warn('Database migrations are never reversed automatically by this command.');

        return self::SUCCESS;
    }
}
