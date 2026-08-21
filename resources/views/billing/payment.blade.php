@extends('layouts.app')

@section('content')
<div style="max-width:760px;margin:0 auto">
    <div class="card">
        <span class="badge">BEP20 USDT</span>
        <h1 style="margin-bottom:6px">Upgrade to {{ $plan['name'] }}</h1>
        <p class="muted">Pay securely with USDT on the BNB Smart Chain (BEP20).</p>
        <div style="font-size:34px;font-weight:900;margin:20px 0;color:#0f172a">USDT {{ number_format((float) $plan['price'], 2) }} <small style="font-size:14px;color:#64748b;font-weight:600">/ month</small></div>

        @if($errors->any())
            <div class="alert error">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <div class="card" style="background:#f8fafc;margin-bottom:20px">
            <h3 style="margin-top:0">1. Send USDT</h3>
            <p class="muted" style="margin-bottom:8px">Network</p>
            <strong>BEP20 / BNB Smart Chain</strong>
            <p class="muted" style="margin:18px 0 7px">USDT receiving address</p>
            <code style="display:block;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:13px;font-size:13px">{{ $paymentAddress ?: 'USDT address is not configured yet.' }}</code>
            <p class="muted" style="margin-bottom:0;font-size:13px">Send exactly <strong>USDT {{ number_format((float) $plan['price'], 2) }}</strong>. Only BEP20 USDT payments are accepted.</p>
        </div>

        <form method="POST" action="{{ route('payments.store') }}">
            @csrf
            <input type="hidden" name="plan" value="{{ $planKey }}">
            <input type="hidden" name="method" value="bep20">

            <label for="transaction_reference">2. Enter blockchain TXID</label>
            <input id="transaction_reference" name="transaction_reference" value="{{ old('transaction_reference') }}" required minlength="6" maxlength="120" placeholder="Paste your BEP20 transaction hash / TXID" autocomplete="off">

            <button class="btn" type="submit" style="width:100%;margin-top:20px">Submit USDT Payment for Verification</button>
        </form>

        <div class="alert" style="margin-top:20px;margin-bottom:0">✓ Your subscription stays pending until an authorized GigRanker administrator verifies the blockchain transaction.</div>
    </div>
</div>
@endsection
