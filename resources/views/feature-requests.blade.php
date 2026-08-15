@extends('layouts.app')
@section('content')
<div class="card"><span class="badge">Community</span><h1>Request a Feature</h1><p class="muted">Tell us what you want added to GigRanker. Requests are reviewed by the admin and can become Free, Paid, or Request-only features.</p>
<form method="POST" action="{{ route('feature-requests.store') }}">@csrf
<label>Feature title<input name="title" maxlength="180" required placeholder="Example: Add WordPress export"></label>
<label>What should it do?<textarea name="description" maxlength="5000" required placeholder="Explain the feature and why it would help your workflow."></textarea></label>
<button class="btn" type="submit">Submit Request</button>
</form></div>
<div class="card" style="margin-top:20px"><h2>My Requests</h2><div style="overflow:auto"><table><thead><tr><th>Feature</th><th>Status</th><th>Pricing</th><th>Updated</th></tr></thead><tbody>@forelse($requests as $item)<tr><td>{{ $item->title }}</td><td><span class="badge">{{ $item->status }}</span></td><td>{{ ucfirst($item->pricing) }}</td><td>{{ $item->updated_at->format('Y-m-d') }}</td></tr>@empty<tr><td colspan="4" class="muted">No requests yet.</td></tr>@endforelse</tbody></table></div></div>
@endsection
