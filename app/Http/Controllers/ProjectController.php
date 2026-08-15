<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\Billing\CreditLedger;
use App\Services\Seo\GigSeoGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'projects' => Auth::user()->projects()->withCount('pages')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('projects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'gig_url' => ['required', 'url', 'max:2048'],
            'site_url' => ['nullable', 'url', 'max:2048'],
            'gig_title' => ['nullable', 'string', 'max:255'],
            'gig_description' => ['nullable', 'string', 'max:10000'],
            'service_category' => ['nullable', 'string', 'max:120'],
            'target_country' => ['nullable', 'string', 'max:120'],
            'target_city' => ['nullable', 'string', 'max:120'],
            'keywords' => ['nullable', 'string', 'max:2000'],
            'brand_name' => ['nullable', 'string', 'max:160'],
            'fiverr_profile_url' => ['nullable', 'url', 'max:2048'],
            'github_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $gigHost = strtolower((string) parse_url($validated['gig_url'], PHP_URL_HOST));
        if (! in_array($gigHost, ['fiverr.com', 'www.fiverr.com'], true)) {
            return back()->withInput()->withErrors(['gig_url' => 'Please provide a valid Fiverr gig URL.']);
        }

        if (! empty($validated['fiverr_profile_url'])) {
            $profileHost = strtolower((string) parse_url($validated['fiverr_profile_url'], PHP_URL_HOST));
            if (! in_array($profileHost, ['fiverr.com', 'www.fiverr.com'], true)) {
                return back()->withInput()->withErrors(['fiverr_profile_url' => 'Please provide a valid Fiverr profile URL.']);
            }
        }

        $validated['site_url'] = ! empty($validated['site_url'])
            ? rtrim($validated['site_url'], '/')
            : null;

        $validated['keywords'] = collect(preg_split('/[,\n]+/', (string) ($validated['keywords'] ?? '')))
            ->map(fn (string $keyword): string => trim($keyword))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $project = Auth::user()->projects()->create($validated + ['status' => 'draft']);

        return redirect()->route('dashboard')->with('success', "Project '{$project->name}' was created.");
    }

    public function generate(Request $request, Project $project, GigSeoGenerator $generator, CreditLedger $ledger): RedirectResponse
    {
        abort_unless($project->user_id === Auth::id(), 404);

        $validated = $request->validate([
            'page_count' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $requestedPages = (int) ($validated['page_count'] ?? 10);
        $reference = 'generation:'.$project->id.':'.Str::uuid();

        try {
            $ledger->reserve(Auth::user(), $requestedPages, 'SEO page generation', $reference);
        } catch (RuntimeException) {
            return back()->withErrors(['credits' => 'You need '.$requestedPages.' AI credits to generate '.$requestedPages.' pages. Please upgrade or add credits.']);
        }

        try {
            $project->update(['status' => 'generating']);
            $pages = $generator->generate($project, $requestedPages);

            if ($pages === []) {
                $ledger->refund(Auth::user(), $requestedPages, 'Failed SEO generation refund', $reference.':refund');
                $project->update(['status' => 'failed']);

                return back()->withErrors(['generation' => 'The AI did not return any usable pages. Your credits were refunded.']);
            }

            foreach ($pages as $page) {
                $project->pages()->updateOrCreate(
                    ['slug' => $page['slug']],
                    $page + ['status' => 'draft'],
                );
            }

            $usedPages = min($requestedPages, count($pages));
            $unusedCredits = $requestedPages - $usedPages;
            if ($unusedCredits > 0) {
                $ledger->refund(Auth::user(), $unusedCredits, 'Unused SEO generation credits', $reference.':unused');
            }

            $project->update(['status' => 'generated']);

            return back()->with('success', count($pages).' SEO pages generated successfully. '.$usedPages.' AI credits used.');
        } catch (Throwable $exception) {
            report($exception);
            $ledger->refund(Auth::user(), $requestedPages, 'Failed SEO generation refund', $reference.':refund');
            $project->update(['status' => 'failed']);

            return back()->withErrors(['generation' => 'Generation is temporarily unavailable. Your credits were refunded.']);
        }
    }
}
