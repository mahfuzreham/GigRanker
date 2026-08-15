<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Services\Deployment\CpanelDeploymentService;
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
            'latest' => null,
        ]);
    }

    public function save(Request $request): RedirectResponse
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
        return back()->with('success', 'Deployment settings saved securely.');
    }

    public function check(CpanelDeploymentService $service): RedirectResponse
    {
        try { $latest = $service->latestGithubCommit(); return back()->with('update', $latest); }
        catch (Throwable $e) { report($e); return back()->withErrors(['deployment' => 'Unable to check GitHub: '.$e->getMessage()]); }
    }

    public function test(CpanelDeploymentService $service): RedirectResponse
    {
        try { $service->test(); return back()->with('success', 'cPanel connection and Git repository access are working.'); }
        catch (Throwable $e) { report($e); return back()->withErrors(['deployment' => 'cPanel test failed: '.$e->getMessage()]); }
    }

    public function approveDeploy(CpanelDeploymentService $service): RedirectResponse
    {
        try { $service->deployApproved(); return back()->with('success', 'Approved update deployed to cPanel.'); }
        catch (Throwable $e) { report($e); return back()->withErrors(['deployment' => 'Deployment failed: '.$e->getMessage()]); }
    }
}
