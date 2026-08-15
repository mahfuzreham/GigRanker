@extends('layouts.app')
@section('content')
<div style="max-width:1000px;margin:auto">
<span class="badge">Admin • Product Updates</span><h1>Feature Updates</h1>
<p class="muted">Publish new features so users can see whether they are free, paid, or available by request.</p>
@if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
<form method="POST" class="card" action="{{ route('admin.feature-updates.store') }}">@csrf
<div class="grid"><label>Feature title<input name="title" required maxlength="180" placeholder="AI SEO Blog Generator"></label><label>Access type<select name="access_type"><option value="free">Free</option><option value="paid">Paid</option><option value="request">By Request</option></select></label><label style="grid-column:1/-1">Summary<textarea name="summary" rows="4" required maxlength="5000" placeholder="Explain what changed and who can use it."></textarea></label></div>
<label style="display:block;margin:12px 0"><input type="checkbox" name="published" value="1"> Publish immediately</label><button class="btn" type="submit">Publish Update</button>
</form>
<div style="margin-top:20px">@foreach($updates as $update)<div class="card" style="margin:12px 0"><div><span class="badge">{{ strtoupper($update->access_type) }}</span> <strong>{{ $update->title }}</strong></div><p>{{ $update->summary }}</p><small class="muted">{{ $update->published ? 'Published' : 'Draft' }}</small></div>@endforeach</div>
{{ $updates->links() }}
</div>
@endsection
