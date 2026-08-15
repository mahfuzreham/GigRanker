<?php

declare(strict_types=1);

namespace App\Services\Deployment;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class DeploymentHistoryService
{
    public function checkGithub(): array
    {
        $repo = trim((string) AppSetting::getValue('github_repo'));
        $branch = trim((string) AppSetting::getValue('github_branch', 'main'));
        $token = AppSetting::getValue('github_token');
        if (!$repo) throw new RuntimeException('GitHub repository is not configured.');
        $request = Http::timeout(15)->acceptJson();
        if ($token) $request = $request->withToken($token);
        $response = $request->get('https://api.github.com/repos/'.ltrim($repo, '/').'/commits', ['sha'=>$branch,'per_page'=>10])->throw();
        return ['repo'=>$repo,'branch'=>$branch,'commits'=>$response->json()];
    }

    public function cpanelConfigured(): bool
    {
        return (bool) AppSetting::getValue('cpanel_host') && (bool) AppSetting::getValue('cpanel_user');
    }
}
