<?php

declare(strict_types=1);

namespace App\Services\Deployment;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class CpanelDeploymentService
{
    private function config(): array
    {
        return [
            'host' => trim((string) AppSetting::getValue('cpanel_host')),
            'port' => (int) AppSetting::getValue('cpanel_port', 2083),
            'user' => trim((string) AppSetting::getValue('cpanel_user')),
            'secret' => (string) AppSetting::getValue('cpanel_secret'),
            'repository_root' => trim((string) AppSetting::getValue('cpanel_repository_root', '/home/gigranker/public_html')),
            'github_repo' => trim((string) AppSetting::getValue('github_repo', 'mahfuzreham/GigRanker')),
            'github_branch' => trim((string) AppSetting::getValue('github_branch', 'main')),
            'github_token' => (string) AppSetting::getValue('github_token'),
        ];
    }

    public function latestGithubCommit(): array
    {
        $c = $this->config();
        if (!preg_match('/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/', $c['github_repo'])) throw new RuntimeException('Invalid GitHub repository.');
        $url = 'https://api.github.com/repos/'.$c['github_repo'].'/commits/'.rawurlencode($c['github_branch']);
        $request = Http::timeout(15)->acceptJson()->withHeaders(['X-GitHub-Api-Version'=>'2022-11-28','User-Agent'=>'GigRanker-Update-Center']);
        if ($c['github_token']) $request = $request->withToken($c['github_token']);
        $data = $request->get($url)->throw()->json();
        return ['sha' => (string) ($data['sha'] ?? ''), 'message' => (string) data_get($data, 'commit.message', ''), 'url' => (string) ($data['html_url'] ?? '')];
    }

    public function test(): array
    {
        $c = $this->config();
        $this->assertConfigured($c);
        $response = $this->uapi($c, 'VersionControl', 'retrieve', ['fields' => 'name,type,repository_root,branch,last_update']);
        return $response;
    }

    public function deployApproved(): array
    {
        $c = $this->config();
        $this->assertConfigured($c);
        // Pull the configured branch from the remote repository, then deploy HEAD.
        $this->uapi($c, 'VersionControl', 'update', ['repository_root' => $c['repository_root'], 'branch' => $c['github_branch']]);
        return $this->uapi($c, 'VersionControlDeployment', 'create', ['repository_root' => $c['repository_root']]);
    }

    private function assertConfigured(array $c): void
    {
        foreach (['host','user','secret','repository_root'] as $key) if ($c[$key] === '') throw new RuntimeException('cPanel deployment is not fully configured.');
        if ($c['port'] !== 2083) throw new RuntimeException('Only secure cPanel API port 2083 is allowed.');
    }

    private function uapi(array $c, string $module, string $function, array $query): array
    {
        $url = 'https://'.$c['host'].':'.$c['port'].'/execute/'.$module.'/'.$function;
        $response = Http::timeout(30)->withHeaders([
            'Authorization' => 'cpanel '.$c['user'].':'.$c['secret'],
            'User-Agent' => 'GigRanker-Deployment-Center',
        ])->get($url, $query)->throw()->json();
        if ((int) data_get($response, 'result.status', 0) !== 1) throw new RuntimeException((string) (data_get($response, 'result.errors.0') ?? 'cPanel API request failed.'));
        return (array) data_get($response, 'result', []);
    }
}
