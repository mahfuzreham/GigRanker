@extends('layouts.app')

@section('content')
<div class="card" style="max-width:720px;margin:0 auto">
    <h1>Pay for {{ $plan['name'] }}</h1>
    <p class="muted">Amount: <strong>{{ $plan['currency'] }} {{ number_format((float) $plan['price'], 2) }}</strong></p>

    @if($errors->any())<div class="card" style="border-color:#b42318;margin-bottom:18px">@foreach($errors->all() as $error)<p style="margin:0 0 6px">{{ $error }}</p>@endforeach</div>@endif

    <div class="card" style="margin-bottom:18px">
        <h3>Choose payment method</h3>
        <p><strong>bKash</strong><br>{{ $bkashNumber ?: 'Payment number will be configured by the administrator.' }}</p>
        <p><strong>BEP20 USDT</strong><br><code>{{ $paymentAddress ?: 'Wallet address will be configured by the administrator.' }}</code></p>
        <p class="muted">Only send the exact amount shown above. Do not send funds until the displayed payment destination has been verified.</p>
    </div>

    @if($bkashAuto)
        <div class="card" style="margin-bottom:18px;border-color:rgba(34,211,238,.35)">
            <h3>⚡ Pay directly with bKash</h3>
            <p class="muted">You will be redirected to the official bKash payment page. After successful payment, GigRanker verifies the payment and activates your subscription.</p>
            <form method="POST" action="{{ route('payments.bkash-start') }}">@csrf<input type="hidden" name="plan" value="{{ $planKey }}"><button class="btn" type="submit">Pay with bKash →</button></form>
        </div>
    @endif

    <div class="card">
        <h3>Manual payment</h3>
        <p class="muted">Use this option if you paid manually to the displayed bKash number or sent BEP20 USDT.</p>
        <form method="POST" action="{{ route('payments.store') }}">
            @csrf
            <input type="hidden" name="plan" value="{{ $planKey }}">
            <label>Payment method</label>
            <select name="method" required style="width:100%;padding:10px;margin:6px 0 16px"><option value="bkash">bKash</option><option value="bep20">BEP20 USDT</option></select>
            <label>Transaction ID / TXID</label>
            <input name="transaction_reference" value="{{ old('transaction_reference') }}" required maxlength="120" style="width:100%;padding:10px;margin:6px 0 18px" placeholder="Enter your transaction ID or blockchain TXID">
            <button class="btn secondary" type="submit">Submit Manual Payment</button>
        </form>
    </div>
</div>
@endsection
