@extends('layouts.app')
@section('content')
<div style="max-width:1100px;margin:auto">
<span class="badge">Admin • AI</span><h1>AI Provider Manager</h1>
<p class="muted">Configure GigRanker’s server-side AI providers. User API keys are not required. API secrets are encrypted at rest.</p>
@if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('admin.ai.update') }}">@csrf
<div class="card"><h2>Routing</h2><div class="grid"><label>Primary provider<select name="primary"><option value="openai" @selected($primary==='openai')>OpenAI</option><option value="anthropic" @selected($primary==='anthropic')>Claude / Anthropic</option><option value="gemini" @selected($primary==='gemini')>Google Gemini</option><option value="custom" @selected($primary==='custom')>Custom OpenAI-compatible</option></select></label><label>Fallback providers <input name="fallbacks" value="{{ $fallbacks }}" placeholder="gemini,anthropic"></label></div></div>
@foreach($providers as $name => $p)
<div class="card" style="margin-top:18px"><h2>{{ $name==='openai'?'OpenAI':($name==='anthropic'?'Claude / Anthropic':($name==='gemini'?'Google Gemini':'Custom OpenAI-Compatible')) }}</h2><div class="grid"><label>API Key<input type="password" name="{{ $name }}_api_key" placeholder="{{ $p['api_key_set'] ? 'Saved — leave blank to keep it' : 'Enter API key' }}" autocomplete="new-password"></label><label>Model<input name="{{ $name }}_model" value="{{ $p['model'] }}" placeholder="Provider model"></label><label style="grid-column:1/-1">Base URL<input type="url" name="{{ $name }}_base_url" value="{{ $p['base_url'] }}" placeholder="https://api.example.com/v1"></label></div><button class="btn secondary" type="submit" formaction="{{ route('admin.ai.test') }}" formmethod="POST" name="provider" value="{{ $name }}">Test {{ ucfirst($name) }}</button></div>
@endforeach
<button class="btn" type="submit" style="margin-top:18px">Save AI Settings</button>
</form></div>
@endsection
