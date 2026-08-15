@extends('layouts.app')
@section('content')
<div style="max-width:1120px;margin:auto">
<span class="badge">Admin • AI Control Center</span><h1>AI Provider Manager</h1>
<p class="muted">One central place for all server-side AI providers. User API keys are never required. Provider secrets are stored encrypted.</p>
@if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card"><strong>Free AI:</strong> OpenRouter can route to currently available free models; Groq may offer free/developer access subject to its current limits. Add your own keys—never hard-code or share keys.</div>
<form method="POST" action="{{ route('admin.ai.update') }}">@csrf
<div class="card" style="margin-top:18px"><h2>⚡ Global Routing</h2><div class="grid"><label>Primary provider<select name="primary">@foreach(['openrouter'=>'OpenRouter • Free Models','groq'=>'Groq','openai'=>'OpenAI','anthropic'=>'Claude / Anthropic','gemini'=>'Google Gemini','custom'=>'Custom OpenAI-compatible'] as $value=>$label)<option value="{{ $value }}" @selected($primary===$value)>{{ $label }}</option>@endforeach</select></label><label>Fallback chain<input name="fallbacks" value="{{ $fallbacks }}" placeholder="groq,openrouter,gemini"></label></div><p class="muted">Example: OpenRouter → Groq → Gemini. Failed providers are skipped.</p></div>
@foreach($providers as $name => $p)
@php($labels=['openai'=>'OpenAI','anthropic'=>'Claude / Anthropic','gemini'=>'Google Gemini','openrouter'=>'OpenRouter • Free Models','groq'=>'Groq • Free/Developer','custom'=>'Custom OpenAI-Compatible'])
@php($defaults=['openrouter'=>'https://openrouter.ai/api/v1','groq'=>'https://api.groq.com/openai/v1'])
<div class="card" style="margin-top:18px"><div style="display:flex;justify-content:space-between;align-items:center;gap:12px"><h2 style="margin:0">{{ $labels[$name] }}</h2><span class="badge">{{ $p['api_key_set'] ? 'Configured' : 'Not configured' }}</span></div><div class="grid" style="margin-top:15px"><label>API Key<input type="password" name="{{ $name }}_api_key" placeholder="{{ $p['api_key_set'] ? 'Saved — leave blank to keep' : 'Enter your own API key' }}" autocomplete="new-password"></label><label>Model<input name="{{ $name }}_model" value="{{ $p['model'] }}" placeholder="{{ $name==='openrouter'?'openrouter/free':($name==='groq'?'llama-3.3-70b-versatile':'Provider model') }}"></label><label style="grid-column:1/-1">Base URL<input type="url" name="{{ $name }}_base_url" value="{{ $p['base_url'] ?: ($defaults[$name] ?? '') }}" placeholder="https://api.example.com/v1"></label></div><label style="display:block;margin:12px 0"><input type="checkbox" name="{{ $name }}_enabled" value="1" @checked($p['enabled'])> Enable this provider</label><button class="btn secondary" type="submit" formaction="{{ route('admin.ai.test') }}" formmethod="POST" name="provider" value="{{ $name }}">Test Connection</button></div>
@endforeach
<div class="card" style="margin-top:18px"><h2>📊 AI Usage & Cost</h2><p class="muted">Usage accounting and per-user credit limits will use this provider routing layer. Provider keys remain server-side and encrypted.</p></div>
<button class="btn" type="submit" style="margin-top:18px">Save All AI Settings</button>
</form></div>
@endsection
