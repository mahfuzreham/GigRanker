<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'projects' => Project::query()->latest()->get(),
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

        $validated['keywords'] = collect(preg_split('/[,\n]+/', (string) ($validated['keywords'] ?? '')))
            ->map(fn (string $keyword): string => trim($keyword))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $project = Project::create($validated + ['status' => 'draft']);

        return redirect()->route('dashboard')->with('success', "Project '{$project->name}' was created.");
    }
}
