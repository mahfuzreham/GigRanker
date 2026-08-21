@extends('layouts.app')

@section('content')
<div style="max-width:1000px;margin:0 auto">
    <div style="margin-bottom:25px">
        <h1 style="margin:0">Admin Settings</h1>
        <p class="muted">Manage AI providers and BEP20 USDT payment settings without editing .env.</p>
    </div>

    @if(session('success'))
        <div class="card" style="margin-bottom:18px">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" style="display:grid;gap:18px">
        @csrf
        <div class="card">
            <h2>AI Provider</h2>
            <label>Primary provider</label>
            <select name="ai_provider">
                @foreach(['gemini','groq','openai'] as $provider)
                    <option value="{{ $provider }}" @selected($settings['ai_provider'] === $provider)>{{ ucfirst($provider) }}</option>
                @endforeach
            </select>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;margin-top:14px">
                <div><label>Gemini API Key</label><input type="password" name="gemini_api_key" placeholder="{{ $settings['gemini_key_set'] ? 'Saved — leave blank to keep' : 'Enter API key' }}" autocomplete="new-password"></div>
                <div><label>Groq API Key</label><input type="password" name="groq_api_key" placeholder="{{ $settings['groq_key_set'] ? 'Saved — leave blank to keep' : 'Enter API key' }}" autocomplete="new-password"></div>
                <div><label>OpenAI API Key</label><input type="password" name="openai_api_key" placeholder="{{ $settings['openai_key_set'] ? 'Saved — leave blank to keep' : 'Enter API key' }}" autocomplete="new-password"></div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;margin-top:14px">
                <div><label>Gemini Model</label><input name="gemini_model" value="{{ $settings['gemini_model'] }}"></div>
                <div><label>Groq Model</label><input name="groq_model" value="{{ $settings['groq_model'] }}"></div>
                <div><label>OpenAI Model</label><input name="openai_model" value="{{ $settings['openai_model'] }}"></div>
            </div>
        </div>

        <div class="card">
            <h2>BEP20 USDT</h2>
            <label>Receiving wallet address</label>
            <input name="bep20_address" value="{{ $settings['bep20_address'] }}" required>
            <label style="margin-top:12px">Network</label>
            <select name="bep20_network"><option value="BSC" selected>BNB Smart Chain (BSC / BEP20)</option></select>
        </div>

        <button class="btn" type="submit">Save Settings</button>
    </form>
</div>
@endsection
