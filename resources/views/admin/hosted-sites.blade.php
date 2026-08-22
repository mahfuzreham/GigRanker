@extends('layouts.app')
@section('content')
<div class="page-head"><div><span class="eyebrow">Client resources</span><h1>Hosted Free Sites</h1><p>Add multiple hosted HTML site links that every client can access from their dashboard.</p></div></div>
<div class="grid">
<div class="card" style="grid-column:span 4"><h2 style="margin-top:0">Add Site Link</h2><form method="POST" action="{{ route('admin.hosted-sites.store') }}">@csrf
<label>Name</label><input name="name" placeholder="Free HTML Template" required>
<label style="margin-top:12px">Site Link</label><input type="url" name="site_link" placeholder="https://example.com/site/" required>
<label style="margin-top:12px">Setup Link</label><input type="url" name="setup_link" placeholder="https://example.com/setup/">
<label style="margin-top:12px">Description</label><textarea name="description" placeholder="Short instructions for clients"></textarea>
<label style="margin-top:12px">Sort Order</label><input type="number" name="sort_order" value="0" min="0">
<button class="btn" style="margin-top:14px;width:100%" type="submit">Add Site</button>
</form></div>
<div class="card" style="grid-column:span 8"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px"><div><h2 style="margin:0">Available Sites</h2><p class="muted" style="margin:4px 0">Only active links are shown to clients.</p></div><span class="badge">{{ $links->where('is_active', true)->count() }} Active</span></div>
<div style="display:grid;gap:12px">
@forelse($links as $link)
<div style="border:1px solid #eaecf0;border-radius:10px;padding:15px"><div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start"><div><strong>{{ $link->name }}</strong><div class="muted" style="font-size:12px;margin-top:4px">{{ $link->description }}</div></div><span class="badge">{{ $link->is_active ? 'Active' : 'Hidden' }}</span></div>
<div style="margin-top:12px;font-size:12px;word-break:break-all"><strong>Site Link:</strong> <a href="{{ $link->site_link }}" target="_blank" rel="noopener" style="color:#465fff">{{ $link->site_link }}</a><br><strong>Setup Link:</strong> {{ $link->setup_link ?: '—' }}</div>
<div style="display:flex;gap:7px;margin-top:12px;flex-wrap:wrap"><form method="POST" action="{{ route('admin.hosted-sites.toggle',$link) }}">@csrf<button class="btn secondary" type="submit">{{ $link->is_active ? 'Hide from Clients' : 'Show to Clients' }}</button></form><form method="POST" action="{{ route('admin.hosted-sites.destroy',$link) }}" onsubmit="return confirm('Delete this site link?')">@csrf @method('DELETE')<button class="btn secondary" type="submit">Delete</button></form></div></div>
@empty<div style="padding:35px;text-align:center;border:1px dashed #d0d5dd;border-radius:10px" class="muted">No hosted sites added yet.</div>@endforelse
</div></div></div>
@endsection
