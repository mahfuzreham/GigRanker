<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Deployment;
use Illuminate\Support\Str;
use Throwable;

class DeploymentLogger
{
    public function start(
        string $environment = 'production',
        ?string $version = null,
        ?string $commitSha = null,
        ?string $triggeredBy = null,
        ?string $source = null,
        array $metadata = [],
        ?int $userId = null,
    ): Deployment {
        return Deployment::query()->create([
            'deployment_id' => (string) Str::uuid(),
            'user_id' => $userId,
            'environment' => $environment,
            'version' => $version,
            'commit_sha' => $commitSha ?: $this->commitSha(),
            'status' => 'running',
            'started_at' => now(),
            'triggered_by' => $triggeredBy,
            'source' => $source,
            'metadata' => $metadata,
        ]);
    }

    public function succeed(Deployment $deployment, ?string $message = null, array $metadata = []): Deployment
    {
        return $this->finish($deployment, 'success', $message, $metadata);
    }

    public function fail(Deployment $deployment, Throwable|string|null $error = null, array $metadata = []): Deployment
    {
        $message = $error instanceof Throwable ? $error->getMessage() : $error;

        if ($error instanceof Throwable) {
            report($error);
        }

        return $this->finish($deployment, 'failed', $message, $metadata);
    }

    private function finish(Deployment $deployment, string $status, ?string $message, array $metadata): Deployment
    {
        $finishedAt = now();
        $startedAt = $deployment->started_at;

        $durationMs = $startedAt !== null
            ? max(0, $startedAt->diffInMilliseconds($finishedAt))
            : null;

        $mergedMetadata = array_merge($deployment->metadata ?? [], $metadata);

        $deployment->update([
            'status' => $status,
            'finished_at' => $finishedAt,
            'duration_ms' => $durationMs,
            'message' => $message,
            'metadata' => $mergedMetadata,
        ]);

        return $deployment->refresh();
    }

    private function commitSha(): ?string
    {
        $sha = getenv('GIT_COMMIT') ?: getenv('GITHUB_SHA');

        return $sha !== false && $sha !== '' ? $sha : null;
    }
}
