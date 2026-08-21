@extends('layouts.app')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:25px;flex-wrap:wrap">
    <div><span class="badge">Workspace</span><h1 style="margin:8px 0 2px">Dashboard</h1><p class="muted" style="margin:0">Manage your gig marketing projects and SEO generation.</p></div>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <span class="card" style="padding:10px 14px;margin:0"><strong>{{ Auth::user()->ai_credits }}</strong> AI credits</span>
        <a class="btn" href="{{ route('projects.create') }}">+ New Project</a>
    </div>
</div>

@if($errors->any())
    <div class="card" style="border-color:#fecaca;background:#fff7f7;margin-bottom:18px">
        <strong style="color:#991b1b">Something needs attention</strong>
        @foreach($errors->all() as $error)
            <p style="margin:6px 0 0;color:#991b1b">{{ $error }}</p>
        @endforeach
    </div>
@endif

@if(session('success'))
    <div class="alert">{{ session('success') }}</div>
@endif

<div class="card">
@if($projects->isEmpty())
    <div style="text-align:center;padding:28px 10px">
        <span class="badge">Get started</span>
        <h2 style="margin:12px 0 6px">Create your first project</h2>
        <p class="muted">Add your freelance gig information and generate SEO-ready pages from one workspace.</p>
        <a class="btn" href="{{ route('projects.create') }}">Create First Project</a>
    </div>
@else
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:15px;flex-wrap:wrap">
        <div><h2 style="margin:0">Your Projects</h2><p class="muted" style="margin:3px 0 0">{{ $projects->count() }} project{{ $projects->count() === 1 ? '' : 's' }}</p></div>
        <a class="btn secondary" href="{{ route('projects.create') }}">New Project</a>
    </div>
    <div style="overflow:auto">
    <table>
        <thead><tr><th>Project</th><th>Gig</th><th>Target</th><th>Pages</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($projects as $project)
            <tr>
                <td><strong>{{ $project->name }}</strong></td>
                <td><a href="{{ $project->gig_url }}" target="_blank" rel="noopener noreferrer">{{ $project->gig_title ?: 'View gig' }}</a></td>
                <td>{{ $project->target_country ?: '—' }}{{ $project->target_city ? ', '.$project->target_city : '' }}</td>
                <td>{{ $project->pages_count }}</td>
                <td><span class="badge">{{ ucfirst($project->status) }}</span></td>
                <td>
                    <div style="display:flex;gap:8px;flex-wrap:wrap">
                        <form method="POST" action="{{ route('projects.generate', $project) }}">
                            @csrf
                            <input type="hidden" name="page_count" value="10">
                            <button class="btn secondary" type="submit" {{ in_array($project->status, ['generating'], true) || Auth::user()->ai_credits < 10 ? 'disabled' : '' }}>Generate 10 SEO Pages</button>
                        </form>
                        @if($project->pages_count > 0)
                            <a class="btn secondary" href="{{ route('projects.preview', $project) }}">Preview</a>
                            <a class="btn secondary" href="{{ route('projects.export', $project) }}">ZIP</a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    <p class="muted" style="margin:16px 0 0">Generation costs 1 AI credit per requested SEO page. Failed generations are refunded. AI credentials are never exposed to the browser.</p>
@endif
</div>
@endsection
