@extends('layouts.app')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:25px;flex-wrap:wrap">
    <div><h1 style="margin:0">Dashboard</h1><p class="muted">Manage your gig marketing projects.</p></div>
    <a class="btn" href="{{ route('projects.create') }}">+ New Project</a>
</div>

<div class="card">
@if($projects->isEmpty())
    <h3>No projects yet</h3>
    <p class="muted">Create your first project by adding your freelance gig information.</p>
    <a class="btn" href="{{ route('projects.create') }}">Create First Project</a>
@else
    <table>
        <thead><tr><th>Project</th><th>Gig</th><th>Target</th><th>Status</th></tr></thead>
        <tbody>
        @foreach($projects as $project)
            <tr>
                <td><strong>{{ $project->name }}</strong></td>
                <td><a href="{{ $project->gig_url }}" target="_blank" rel="noopener noreferrer">{{ $project->gig_title ?: 'View gig' }}</a></td>
                <td>{{ $project->target_country ?: '—' }}{{ $project->target_city ? ', '.$project->target_city : '' }}</td>
                <td>{{ ucfirst($project->status) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
</div>
@endsection
