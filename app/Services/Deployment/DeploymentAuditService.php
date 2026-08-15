<?php

declare(strict_types=1);

namespace App\Services\Deployment;

use App\Models\DeploymentLog;
use Illuminate\Http\Request;
use Throwable;

final class DeploymentAuditService
{
    public function record(Request $request, string $action, string $status, array $data = []): DeploymentLog
    {
        return DeploymentLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'status' => $status,
            'repository' => $data['repository'] ?? null,
            'branch' => $data['branch'] ?? null,
            'commit_sha' => $data['commit_sha'] ?? null,
            'commit_message' => $data['commit_message'] ?? null,
            'details' => isset($data['details']) ? json_encode($data['details'], JSON_UNESCAPED_SLASHES) : null,
            'ip_address' => $request->ip(),
        ]);
    }

    public function exceptionDetails(Throwable $exception): array
    {
        return ['type' => $exception::class, 'message' => $exception->getMessage()];
    }
}
