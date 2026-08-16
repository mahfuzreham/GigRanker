@extends('layouts.app')

@section('content')
<div class="card" style="max-width:720px;margin:0 auto">
    <h1>Pay for {{ $plan['name'] }}</h1>
    <p class="muted">Amount: <strong>{{ $plan['currency'] }} {{ number_format((float) $plan['price'], 2) }}</strong></p>

    @if($errors->any())
        <div class="card" style="margin-bottom:18px">
            @foreach($errors->all() as $error)<p style="margin:0 0 6px">{{ $error }}</p>@endforeach
        </div>
    @endif

    <div class="card" style="margin-bottom:18px">
        <h3>Payment destinations</h3>
        <p><strong>bKash</strong><br>{{ $bkashNumber ?: 'Not configured yet.' }}</p>
        <p><strong>BEP20 USDT ({{ $bep20Network }})</strong><br><code>{{ $paymentAddress ?: 'Not configured yet.' }}</code></p>
        <p class="muted">Send only the exact amount shown above. Your plan remains pending until an authorized verification step confirms the payment.</p>
    </div>

    <form method="POST" action="{{ route('payments.store') }}">
        @csrf
        <input type="hidden" name="plan" value="{{ $planKey }}">

        <label>Payment method</label>
        <select name="method" required style="width:100%;padding:10px;margin:6px 0 16px">
            <option value="bkash">bKash</option>
            <option value="bep20">BEP20 USDT</option>
        </select>

        <label>Transaction ID / TXID</label>
        <input name="transaction_reference" value="{{ old('transaction_reference') }}" required minlength="6" maxlength="120" style="width:100%;padding:10px;margin:6px 0 18px" placeholder="Enter your transaction ID or blockchain TXID">

        <button class="btn" type="submit">Submit for Verification</button>
    </form>
</div>
@endsection
