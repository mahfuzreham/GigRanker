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
        $this->assertGithubRepository($c);
        $data = $this->githubRequest($c)->get($this->githubUrl($c, '/commits/'.rawurlencode($c['github_branch'])))->throw()->json();
        return [
            'sha' => (string) ($data['sha'] ?? ''),
            'message' => (string) data_get($data, 'commit.message', ''),
            'url' => (string) ($data['html_url'] ?? ''),
        ];
    }

    public function test(): array
    {
        $c = $this->config();
        $this->assertConfigured($c);
        return $this->uapi($c, 'VersionControl', 'retrieve', ['fields' => 'name,type,repository_root,branch,last_update']);
    }

    public function deployApproved(): array
    {
        $c = $this->config();
        $this->assertConfigured($c);
        $this->uapi($c, 'VersionControl', 'update', ['repository_root' => $c['repository_root'], 'branch' => $c['github_branch']]);
        return $this->uapi($c, 'VersionControlDeployment', 'create', ['repository_root' => $c['repository_root']]);
    }

    /**
     * Create a new commit whose tree matches the selected previously deployed
     * commit, while keeping the current branch history intact. cPanel can then
     * fast-forward to this new rollback commit and deploy it normally.
     */
    public function rollbackToCommit(string $targetSha): array
    {
        $c = $this->config();
        $this->assertConfigured($c);
        $targetSha = strtolower(trim($targetSha));
        if (!preg_match('/^[0-9a-f]{40}$/', $targetSha)) {
            throw new RuntimeException('Invalid rollback commit.');
        }

        $current = $this->githubRequest($c)->get($this->githubUrl($c, '/commits/'.rawurlencode($c['github_branch'])))->throw()->json();
        $currentSha = strtolower((string) ($current['sha'] ?? ''));
        if (!preg_match('/^[0-9a-f]{40}$/', $currentSha)) {
            throw new RuntimeException('Unable to determine current GitHub branch head.');
        }
        if ($currentSha === $targetSha) {
            throw new RuntimeException('The selected release is already the current branch head.');
        }

        $target = $this->githubRequest($c)->get($this->githubUrl($c, '/commits/'.rawurlencode($targetSha)))->throw()->json();
        $targetTree = strtolower((string) data_get($target, 'commit.tree.sha', ''));
        if (!preg_match('/^[0-9a-f]{40}$/', $targetTree)) {
            throw new RuntimeException('The selected release does not have a valid Git tree.');
        }

        $commit = $this->githubRequest($c)->post($this->githubUrl($c, '/git/commits'), [
            'message' => 'Rollback production to '.$targetSha,
            'tree' => $targetTree,
            'parents' => [$currentSha],
        ])->throw()->json();
        $rollbackSha = strtolower((string) ($commit['sha'] ?? ''));
        if (!preg_match('/^[0-9a-f]{40}$/', $rollbackSha)) {
            throw new RuntimeException('GitHub did not return the rollback commit SHA.');
        }

        $this->githubRequest($c)->patch($this->githubUrl($c, '/git/refs/heads/'.rawurlencode($c['github_branch'])), [
            'sha' => $rollbackSha,
            'force' => false,
        ])->throw();

        // The rollback is a normal fast-forward commit, so cPanel's documented
        // --ff-only deployment flow remains intact.
        $this->uapi($c, 'VersionControl', 'update', ['repository_root' => $c['repository_root'], 'branch' => $c['github_branch']]);
        $deployment = $this->uapi($c, 'VersionControlDeployment', 'create', ['repository_root' => $c['repository_root']]);

        return [
            'target_sha' => $targetSha,
            'rollback_sha' => $rollbackSha,
            'target_message' => (string) data_get($target, 'commit.message', ''),
            'deployment' => $deployment,
        ];
    }

    private function assertConfigured(array $c): void
    {
        foreach (['host','user','secret','repository_root'] as $key) {
            if ($c[$key] === '') throw new RuntimeException('cPanel deployment is not fully configured.');
        }
        if ($c['port'] !== 2083) throw new RuntimeException('Only secure cPanel API port 2083 is allowed.');
        $this->assertGithubRepository($c);
    }

    private function assertGithubRepository(array $c): void
    {
        if (!preg_match('/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/', $c['github_repo'])) {
            throw new RuntimeException('Invalid GitHub repository.');
        }
    }

    private function githubUrl(array $c, string $path): string
    {
        return 'https://api.github.com/repos/'.$c['github_repo'].$path;
    }

    private function githubRequest(array $c): \Illuminate\Http\Client\PendingRequest
    {
        $request = Http::timeout(30)->acceptJson()->withHeaders([
            'Accept' => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2022-11-28',
            'User-Agent' => 'GigRanker-Update-Center',
        ]);
        if ($c['github_token']) $request = $request->withToken($c['github_token']);
        return $request;
    }

    private function uapi(array $c, string $module, string $function, array $query): array
    {
        $url = 'https://'.$c['host'].':'.$c['port'].'/execute/'.$module.'/'.$function;
        $response = Http::timeout(30)->withHeaders([
            'Authorization' => 'cpanel '.$c['user'].':'.$c['secret'],
            'User-Agent' => 'GigRanker-Deployment-Center',
        ])->get($url, $query)->throw()->json();
        if ((int) data_get($response, 'result.status', 0) !== 1) {
            throw new RuntimeException((string) (data_get($response, 'result.errors.0') ?? 'cPanel API request failed.'));
        }
        return (array) data_get($response, 'result', []);
    }
}
