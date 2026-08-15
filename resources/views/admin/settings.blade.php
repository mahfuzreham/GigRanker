@extends('layouts.app')

@section('content')
<div style="max-width:980px;margin:auto">
    <span class="badge">Admin Settings</span>
    <h1 style="font-size:38px;line-height:1.1;margin:10px 0 6px">Payments, Binance & Email</h1>
    <p class="muted">Configure provider details from the admin panel. Secrets are encrypted before storage and are never committed to GitHub.</p>

    @if(session('success'))<div class="card" style="margin:18px 0;border-color:rgba(34,197,94,.35)">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="card" style="margin:18px 0;border-color:rgba(239,68,68,.35)"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        <div class="card" style="margin-top:18px">
            <h2>General & Email</h2>
            <div class="grid">
                <label>Site name<input name="site_name" value="{{ old('site_name',$settings['site_name']) }}" required></label>
                <label>Support email<input type="email" name="support_email" value="{{ old('support_email',$settings['support_email']) }}"></label>
                <label>Mail from name<input name="mail_from_name" value="{{ old('mail_from_name',$settings['mail_from_name']) }}" required></label>
                <label>Mail from address<input type="email" name="mail_from_address" value="{{ old('mail_from_address',$settings['mail_from_address']) }}"></label>
            </div>
        </div>

        <div class="card" style="margin-top:18px">
            <h2>bKash</h2>
            <p class="muted">Store your merchant/payment number here. Automatic verification stays disabled until an official supported bKash merchant verification flow is configured.</p>
            <label>bKash payment number<input name="bkash_number" value="{{ old('bkash_number',$settings['bkash_number']) }}"></label>
            <label style="display:block;margin-top:12px"><input type="checkbox" name="bkash_auto_verify" value="1" @checked($settings['bkash_auto_verify'] === '1')> Enable automatic bKash verification</label>
        </div>

        <div class="card" style="margin-top:18px">
            <h2>BEP20 / USDT</h2>
            <p class="muted">Use your receiving wallet and official USDT contract. Never enter a private wallet key here.</p>
            <div class="grid">
                <label>Payment address<input name="bep20_payment_address" value="{{ old('bep20_payment_address',$settings['bep20_payment_address']) }}"></label>
                <label>USDT contract<input name="bep20_usdt_contract" value="{{ old('bep20_usdt_contract',$settings['bep20_usdt_contract']) }}"></label>
                <label style="grid-column:1/-1">BSC RPC URL<input name="bep20_rpc_url" value="{{ old('bep20_rpc_url',$settings['bep20_rpc_url']) }}"></label>
            </div>
        </div>

        <div class="card" style="margin-top:18px">
            <h2>Binance Personal Account API</h2>
            <p class="muted">For a personal Binance account, create an API key in Binance yourself. GigRanker only stores the credentials you provide. <strong>Do not enable withdrawals.</strong> Prefer read-only permissions and IP restrictions.</p>
            <div class="grid">
                <label>API key<input name="binance_api_key" value="{{ old('binance_api_key',$settings['binance_api_key']) }}" autocomplete="off"></label>
                <label>API secret<input type="password" name="binance_api_secret" placeholder="{{ $settings['binance_api_secret_set'] ? 'Secret already saved — leave blank to keep it' : 'Enter API secret' }}" autocomplete="new-password"></label>
            </div>
            <label style="display:block;margin-top:12px"><input type="checkbox" name="binance_enabled" value="1" @checked($settings['binance_enabled'] === '1')> Enable Binance integration</label>
        </div>

        <button class="btn" type="submit" style="margin-top:18px">Save Settings</button>
    </form>
</div>
@endsection
