<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Deployment;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class DeploymentRollbackService
{
    public function rollback(Deployment $target, bool $execute = false): Deployment
    {
        if ($target->status !== 'success') {
            throw new RuntimeException('Only successful deployments can be used as rollback targets.');
        }

        if (! is_string($target->commit_sha) || ! preg_match('/^[0-9a-f]{7,64}$/i', $target->commit_sha)) {
            throw new RuntimeException('The rollback target does not contain a valid Git commit SHA.');
        }

        $currentCommit = $this->git(['rev-parse', 'HEAD']);

        $rollback = Deployment::query()->create([
            'deployment_id' => (string) str()->uuid(),
            'environment' => $target->environment,
            'version' => $target->version,
            'commit_sha' => $target->commit_sha,
            'status' => 'started',
            'started_at' => now(),
            'triggered_by' => 'rollback',
            'source' => 'gigranker:rollback',
            'message' => $execute ? 'Rollback execution started.' : 'Rollback plan created; execution not requested.',
            'metadata' => [
                'rollback_target_deployment_id' => $target->deployment_id,
                'previous_commit_sha' => trim($currentCommit),
                'executed' => $execute,
            ],
        ]);

        if (! $execute) {
            $rollback->update([
                'status' => 'success',
                'finished_at' => now(),
                'duration_ms' => 0,
                'message' => 'Rollback target validated. Re-run with --execute to change the working tree.',
            ]);

            return $rollback->refresh();
        }

        try {
            $this->git(['fetch', '--all', '--tags', '--prune']);
            $this->git(['cat-file', '-e', $target->commit_sha.'^{commit}']);
            $this->git(['reset', '--hard', $target->commit_sha]);

            $rollback->update([
                'status' => 'success',
                'finished_at' => now(),
                'duration_ms' => now()->diffInMilliseconds($rollback->started_at),
                'message' => 'Application code rolled back. Database migrations were not reversed automatically.',
                'metadata' => array_merge($rollback->metadata ?? [], [
                    'result_commit_sha' => trim($this->git(['rev-parse', 'HEAD'])),
                ]),
            ]);
        } catch (\Throwable $exception) {
            $rollback->update([
                'status' => 'failed',
                'finished_at' => now(),
                'duration_ms' => now()->diffInMilliseconds($rollback->started_at),
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        return $rollback->refresh();
    }

    private function git(array $arguments): string
    {
        $result = Process::timeout(120)->run(array_merge(['git'], $arguments));

        if ($result->failed()) {
            throw new RuntimeException(trim($result->errorOutput()) ?: 'Git command failed.');
        }

        return trim($result->output());
    }
}
