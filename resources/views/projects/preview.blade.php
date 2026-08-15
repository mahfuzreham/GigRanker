@extends('layouts.app')

@section('content')
<div class="card" style="margin-bottom:20px">
    <div style="display:flex;justify-content:space-between;gap:15px;align-items:center;flex-wrap:wrap">
        <div>
            <p class="muted" style="margin:0">Website Preview</p>
            <h1 style="margin:5px 0">{{ $project->name }}</h1>
            <p class="muted" style="margin:0">{{ $pages->count() }} generated SEO pages</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a class="btn secondary" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="btn" href="{{ route('projects.export', $project) }}">Download ZIP</a>
        </div>
    </div>
</div>

<div class="grid">
    @foreach($pages as $page)
        <article class="card">
            <p class="muted" style="margin-top:0">{{ ucfirst($page->page_type) }}</p>
            <h2>{{ $page->title }}</h2>
            <p>{{ $page->meta_description ?: 'No meta description supplied.' }}</p>
            <a href="#page-{{ $page->id }}">View content ↓</a>
        </article>
    @endforeach
</div>

<div style="margin-top:20px">
@foreach($pages as $page)
    <article id="page-{{ $page->id }}" class="card" style="margin-bottom:18px">
        <p class="muted">{{ $page->slug }}.html</p>
        <h2>{{ $page->title }}</h2>
        <p><strong>Meta:</strong> {{ $page->meta_description }}</p>
        <div style="white-space:pre-wrap;color:#cbd5e1">{{ $page->content }}</div>
        <p><a class="btn" href="{{ route('projects.click', ['project' => $project->id, 'page' => $page->id]) }}" target="_blank" rel="nofollow sponsored">Test Fiverr CTA</a></p>
    </article>
@endforeach
</div>
@endsection
