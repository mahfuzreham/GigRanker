@extends('layouts.app')
@section('content')
<div style="max-width:1000px;margin:auto">
    <span class="badge">GigRanker Updates</span>
    <h1>New Features & Updates</h1>
    <p class="muted">See what is new, what is free, what requires a paid plan, and which features are available by request.</p>
    @foreach($updates as $update)
        <article class="card" style="margin:18px 0">
            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap">
                <div><span class="badge">{{ strtoupper($update->access_type) }}</span><h2 style="margin:10px 0 4px">{{ $update->title }}</h2><p class="muted" style="margin:0">{{ optional($update->published_at)->format('M d, Y') }}</p></div>
                @if($update->access_type === 'paid')<span class="badge">Paid feature</span>@elseif($update->access_type === 'request')<span class="badge">Request access</span>@else<span class="badge">Free</span>@endif
            </div>
            <p>{{ $update->summary }}</p>
            @if($update->access_type === 'request')
                <a class="btn secondary" href="mailto:{{ $supportEmail }}?subject={{ rawurlencode('GigRanker feature request: '.$update->title) }}">Request this feature</a>
            @endif
        </article>
    @endforeach
    {{ $updates->links() }}
</div>
@endsection
