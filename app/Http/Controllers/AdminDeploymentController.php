<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\DeploymentLog;
use App\Services\Deployment\CpanelDeploymentService;
use App\Services\Deployment\DeploymentAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class AdminDeploymentController extends Controller
{
    public function index(): View
    {
        return view('admin.deployment', [
            'host' => AppSetting::getValue('cpanel_host', ''),
            'port' => AppSetting::getValue('cpanel_port', 2083),
            'user' => AppSetting::getValue('cpanel_user', ''),
            'repository_root' => AppSetting::getValue('cpanel_repository_root', '/home/gigranker/public_html'),
            'github_repo' => AppSetting::getValue('github_repo', 'mahfuzreham/GigRanker'),
            'github_branch' => AppSetting::getValue('github_branch', 'main'),
            'secret_set' => AppSetting::getValue('cpanel_secret', null) !== null,
            'github_token_set' => AppSetting::getValue('github_token', null) !== null,
            'logs' => DeploymentLog::with('user')->latest()->limit(30)->get(),
            'releases' => DeploymentLog::query()->where('action', 'deploy')->where('status', 'success')->whereNotNull('commit_sha')->latest()->limit(15)->get(),
        ]);
    }

    public function save(Request $request, DeploymentAuditService $audit): RedirectResponse
    {
        $data = $request->validate([
            'host' => ['required','string','max:255'], 'port' => ['required','integer','in:2083'], 'user' => ['required','string','max:100'],
            'secret' => ['nullable','string','max:1000'], 'repository_root' => ['required','string','max:500'],
            'github_repo' => ['required','regex:/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/'], 'github_branch' => ['required','string','max:100'],
            'github_token' => ['nullable','string','max:1000'],
        ]);
        AppSetting::putValue('cpanel_host', trim($data['host']));
        AppSetting::putValue('cpanel_port', (string) $data['port']);
        AppSetting::putValue('cpanel_user', trim($data['user']));
        if ($data['secret'] !== null && $data['secret'] !== '') AppSetting::putValue('cpanel_secret', $data['secret'], true);
        AppSetting::putValue('cpanel_repository_root', trim($data['repository_root']));
        AppSetting::putValue('github_repo', trim($data['github_repo']));
        AppSetting::putValue('github_branch', trim($data['github_branch']));
        if ($data['github_token'] !== null && $data['github_token'] !== '') AppSetting::putValue('github_token', $data['github_token'], true);
        $audit->record($request, 'settings_saved', 'success', ['repository' => trim($data['github_repo']), 'branch' => trim($data['github_branch'])]);
        return back()->with('success', 'Deployment settings saved securely.');
    }

    public function check(Request $request, CpanelDeploymentService $service, DeploymentAuditService $audit): RedirectResponse
    {
        try {
            $latest = $service->latestGithubCommit();
            $audit->record($request, 'github_check', 'success', ['repository' => AppSetting::getValue('github_repo'), 'branch' => AppSetting::getValue('github_branch'), 'commit_sha' => $latest['sha'], 'commit_message' => $latest['message']]);
            return back()->with('update', $latest);
        } catch (Throwable $e) {
            report($e);
            $audit->record($request, 'github_check', 'failed', ['repository' => AppSetting::getValue('github_repo'), 'branch' => AppSetting::getValue('github_branch'), 'details' => $audit->exceptionDetails($e)]);
            return back()->withErrors(['deployment' => 'Unable to check GitHub: '.$e->getMessage()]);
        }
    }

    public function test(Request $request, CpanelDeploymentService $service, DeploymentAuditService $audit): RedirectResponse
    {
        try { $service->test(); $audit->record($request, 'cpanel_test', 'success', ['repository' => AppSetting::getValue('github_repo'), 'branch' => AppSetting::getValue('github_branch')]); return back()->with('success', 'cPanel connection and Git repository access are working.'); }
        catch (Throwable $e) { report($e); $audit->record($request, 'cpanel_test', 'failed', ['details' => $audit->exceptionDetails($e)]); return back()->withErrors(['deployment' => 'cPanel test failed: '.$e->getMessage()]); }
    }

    public function approveDeploy(Request $request, CpanelDeploymentService $service, DeploymentAuditService $audit): RedirectResponse
    {
        try {
            $latest = $service->latestGithubCommit();
            $service->deployApproved();
            $audit->record($request, 'deploy', 'success', ['repository' => AppSetting::getValue('github_repo'), 'branch' => AppSetting::getValue('github_branch'), 'commit_sha' => $latest['sha'], 'commit_message' => $latest['message']]);
            return back()->with('success', 'Approved update deployed to cPanel.');
        } catch (Throwable $e) {
            report($e);
            $audit->record($request, 'deploy', 'failed', ['repository' => AppSetting::getValue('github_repo'), 'branch' => AppSetting::getValue('github_branch'), 'details' => $audit->exceptionDetails($e)]);
            return back()->withErrors(['deployment' => 'Deployment failed: '.$e->getMessage()]);
        }
    }

    public function rollback(Request $request, CpanelDeploymentService $service, DeploymentAuditService $audit): RedirectResponse
    {
        $data = $request->validate([
            'release_id' => ['required','integer','exists:deployment_logs,id'],
            'confirmed' => ['accepted'],
        ]);
        $release = DeploymentLog::query()->whereKey($data['release_id'])->where('action', 'deploy')->where('status', 'success')->whereNotNull('commit_sha')->firstOrFail();

        try {
            $result = $service->rollbackToCommit((string) $release->commit_sha);
            $audit->record($request, 'rollback', 'success', [
                'repository' => AppSetting::getValue('github_repo'),
                'branch' => AppSetting::getValue('github_branch'),
                'commit_sha' => $result['rollback_sha'],
                'commit_message' => 'Rollback to '.$release->commit_sha,
                'target_commit_sha' => $release->commit_sha,
                'target_release_id' => $release->id,
            ]);
            return back()->with('success', 'Rollback commit created and approved release deployed successfully.');
        } catch (Throwable $e) {
            report($e);
            $audit->record($request, 'rollback', 'failed', [
                'repository' => AppSetting::getValue('github_repo'),
                'branch' => AppSetting::getValue('github_branch'),
                'target_commit_sha' => $release->commit_sha,
                'target_release_id' => $release->id,
                'details' => $audit->exceptionDetails($e),
            ]);
            return back()->withErrors(['deployment' => 'Rollback failed: '.$e->getMessage()]);
        }
    }
}
