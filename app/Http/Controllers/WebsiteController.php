<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\Seo\StaticSiteExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class WebsiteController extends Controller
{
    public function preview(Project $project): View
    {
        abort_unless($project->user_id === Auth::id(), 404);

        return view('projects.preview', [
            'project' => $project,
            'pages' => $project->pages()->orderBy('id')->get(),
        ]);
    }

    public function export(Project $project, StaticSiteExporter $exporter): BinaryFileResponse
    {
        abort_unless($project->user_id === Auth::id(), 404);

        try {
            $path = $exporter->export($project);
        } catch (Throwable $exception) {
            report($exception);
            abort(422, 'Unable to export this project. Generate SEO pages first and verify the server ZIP extension is enabled.');
        }

        return response()->download($path, 'gigranker-'.$project->id.'.zip')->deleteFileAfterSend(true);
    }

    public function click(Request $request, Project $project)
    {
        $pageId = $request->integer('page');
        $page = $pageId > 0
            ? $project->pages()->whereKey($pageId)->first()
            : null;

        $ip = $request->ip();
        $project->clicks()->create([
            'project_page_id' => $page?->id,
            'ip_hash' => $ip ? hash_hmac('sha256', $ip, (string) config('app.key')) : null,
            'referrer' => mb_substr((string) $request->header('referer'), 0, 2048),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
        ]);

        return redirect()->away($project->gig_url);
    }
}
