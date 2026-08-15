@extends('layouts.app')
@section('content')
<div style="max-width:980px;margin:auto">
<span class="badge">Admin Settings</span><h1>Payments, Binance & Email</h1>
<p class="muted">Configure provider details from the admin panel. Secrets are encrypted at rest and never committed to GitHub.</p>
@if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('admin.settings.update') }}">@csrf
<div class="card"><h2>General & Email</h2><div class="grid">
<label>Site name<input name="site_name" value="{{ old('site_name',$settings['site_name']) }}" required></label>
<label>Support email<input type="email" name="support_email" value="{{ old('support_email',$settings['support_email']) }}"></label>
<label>Mail from name<input name="mail_from_name" value="{{ old('mail_from_name',$settings['mail_from_name']) }}" required></label>
<label>Mail from address<input type="email" name="mail_from_address" value="{{ old('mail_from_address',$settings['mail_from_address']) }}"></label>
</div></div>
<div class="card" style="margin-top:18px"><h2>bKash</h2><p class="muted">Merchant/payment number can be configured here. Automatic verification must only be enabled after official merchant credentials and a supported verification flow are configured.</p><label>bKash payment number<input name="bkash_number" value="{{ old('bkash_number',$settings['bkash_number']) }}"></label><label><input type="checkbox" name="bkash_auto_verify" value="1" @checked($settings['bkash_auto_verify'] === '1')> Enable automatic bKash verification</label></div>
<div class="card" style="margin-top:18px"><h2>BEP20 / USDT</h2><p class="muted">Receiving wallet and official USDT contract. Never enter a private wallet key.</p><div class="grid"><label>Payment address<input name="bep20_payment_address" value="{{ old('bep20_payment_address',$settings['bep20_payment_address']) }}"></label><label>USDT contract<input name="bep20_usdt_contract" value="{{ old('bep20_usdt_contract',$settings['bep20_usdt_contract']) }}"></label><label style="grid-column:1/-1">BSC RPC URL<input name="bep20_rpc_url" value="{{ old('bep20_rpc_url',$settings['bep20_rpc_url']) }}"></label></div></div>
<div class="card" style="margin-top:18px"><h2>Binance Personal Account API</h2><p class="muted">Create the API key yourself in Binance. Use read-only permissions, restrict the key by IP where possible, and <strong>never enable withdrawals</strong> for GigRanker.</p><div class="grid"><label>API key<input name="binance_api_key" value="{{ old('binance_api_key',$settings['binance_api_key']) }}" autocomplete="off"></label><label>API secret<input type="password" name="binance_api_secret" placeholder="{{ $settings['binance_api_secret_set'] ? 'Saved — leave blank to keep it' : 'Enter API secret' }}" autocomplete="new-password"></label></div><label><input type="checkbox" name="binance_enabled" value="1" @checked($settings['binance_enabled'] === '1')> Enable Binance integration</label><button class="btn secondary" type="submit" formaction="{{ route('admin.settings.binance-test') }}" formmethod="POST" style="margin-top:12px">Test Binance API</button></div>
<button class="btn" type="submit" style="margin-top:18px">Save Settings</button>
</form></div>
@endsection
