@extends('layouts.app')
@section('content')
<div style="max-width:1100px;margin:auto">
<span class="badge">Admin • AI</span><h1>AI Provider Manager</h1>
<p class="muted">Server-side AI keys only. Never paste a key into public code or share one between unrelated accounts.</p>
@if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card"><strong>💡 Free option:</strong> OpenRouter currently offers free models through <code>openrouter/free</code>; Groq may also have free developer access subject to its current limits. You must create and supply your own provider key.</div>
<form method="POST" action="{{ route('admin.ai.update') }}">@csrf
<div class="card" style="margin-top:18px"><h2>Routing</h2><div class="grid"><label>Primary provider<select name="primary">
@foreach(['openrouter'=>'OpenRouter (Free Models)','groq'=>'Groq','openai'=>'OpenAI','anthropic'=>'Claude / Anthropic','gemini'=>'Google Gemini','custom'=>'Custom OpenAI-compatible'] as $value=>$label)<option value="{{ $value }}" @selected($primary===$value)>{{ $label }}</option>@endforeach
</select></label><label>Fallback providers<input name="fallbacks" value="{{ $fallbacks }}" placeholder="groq,openrouter,gemini"></label></div></div>
@foreach($providers as $name => $p)
@php($labels=['openai'=>'OpenAI','anthropic'=>'Claude / Anthropic','gemini'=>'Google Gemini','openrouter'=>'OpenRouter • Free Models','groq'=>'Groq • Free/Developer Tier','custom'=>'Custom OpenAI-Compatible'])
@php($defaults=['openrouter'=>'https://openrouter.ai/api/v1','groq'=>'https://api.groq.com/openai/v1'])
<div class="card" style="margin-top:18px"><h2>{{ $labels[$name] }}</h2><div class="grid"><label>API Key<input type="password" name="{{ $name }}_api_key" placeholder="{{ $p['api_key_set'] ? 'Saved — leave blank to keep it' : 'Enter your own API key' }}" autocomplete="new-password"></label><label>Model<input name="{{ $name }}_model" value="{{ $p['model'] }}" placeholder="{{ $name==='openrouter'?'openrouter/free':'Provider model' }}"></label><label style="grid-column:1/-1">Base URL<input type="url" name="{{ $name }}_base_url" value="{{ $p['base_url'] ?: ($defaults[$name] ?? '') }}" placeholder="https://api.example.com/v1"></label></div><button class="btn secondary" type="submit" formaction="{{ route('admin.ai.test') }}" formmethod="POST" name="provider" value="{{ $name }}">Test {{ $labels[$name] }}</button></div>
@endforeach
<button class="btn" type="submit" style="margin-top:18px">Save AI Settings</button>
</form></div>
@endsection
