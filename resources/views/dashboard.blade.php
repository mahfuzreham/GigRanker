@extends('layouts.app')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:25px;flex-wrap:wrap">
    <div><h1 style="margin:0">Dashboard</h1><p class="muted">Manage your gig marketing projects.</p></div>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <span class="card" style="padding:10px 14px;margin:0"><strong>{{ Auth::user()->ai_credits }}</strong> AI credits</span>
        <a class="btn" href="{{ route('projects.create') }}">+ New Project</a>
    </div>
</div>

@if($errors->any())
    <div class="card" style="border-color:#b42318;margin-bottom:18px">
        @foreach($errors->all() as $error)
            <p style="margin:0 0 6px">{{ $error }}</p>
        @endforeach
    </div>
@endif

@if(session('success'))
    <div class="card" style="margin-bottom:18px">
        {{ session('success') }}
    </div>
@endif

<div class="card">
@if($projects->isEmpty())
    <h3>No projects yet</h3>
    <p class="muted">Create your first project by adding your freelance gig information.</p>
    <a class="btn" href="{{ route('projects.create') }}">Create First Project</a>
@else
    <table>
        <thead><tr><th>Project</th><th>Gig</th><th>Target</th><th>Pages</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($projects as $project)
            <tr>
                <td><strong>{{ $project->name }}</strong></td>
                <td><a href="{{ $project->gig_url }}" target="_blank" rel="noopener noreferrer">{{ $project->gig_title ?: 'View gig' }}</a></td>
                <td>{{ $project->target_country ?: '—' }}{{ $project->target_city ? ', '.$project->target_city : '' }}</td>
                <td>{{ $project->pages_count }}</td>
                <td>{{ ucfirst($project->status) }}</td>
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
    <p class="muted" style="margin-bottom:0">Generation costs 1 AI credit per requested SEO page. Failed generations are refunded. AI credentials are never exposed to the browser.</p>
@endif
</div>
@endsection
