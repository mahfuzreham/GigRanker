@extends('layouts.app')

@section('content')
<div style="max-width:1200px;margin:0 auto">
    <div style="margin-bottom:25px">
        <h1 style="margin:0">Payment Verification</h1>
        <p class="muted">Review pending bKash and BEP20 payment submissions.</p>
    </div>

    @if(session('success'))
        <div class="card" style="margin-bottom:18px">{{ session('success') }}</div>
    @endif

    @if($payments->count() === 0)
        <div class="card">No pending payments.</div>
    @else
        <div style="display:grid;gap:15px">
            @foreach($payments as $payment)
                <div class="card">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px">
                        <div><strong>User</strong><br>{{ $payment->user->name }}<br><span class="muted">{{ $payment->user->email }}</span></div>
                        <div><strong>Plan</strong><br>{{ ucfirst($payment->plan) }}</div>
                        <div><strong>Amount</strong><br>{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</div>
                        <div><strong>Method</strong><br>{{ strtoupper($payment->method) }}</div>
                        <div><strong>TXID</strong><br><code>{{ $payment->transaction_reference }}</code></div>
                        <div><strong>Submitted</strong><br>{{ $payment->created_at->toDateTimeString() }}</div>
                    </div>
                    <div style="display:flex;gap:10px;margin-top:16px">
                        <form method="POST" action="{{ route('admin.payments.approve', $payment) }}">
                            @csrf
                            <button class="btn" type="submit" onclick="return confirm('Approve this payment and activate the paid subscription?')">Approve & Activate</button>
                        </form>
                        <form method="POST" action="{{ route('admin.payments.reject', $payment) }}">
                            @csrf
                            <button class="btn" type="submit" onclick="return confirm('Reject this payment?')">Reject</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top:20px">{{ $payments->links() }}</div>
    @endif
</div>
@endsection
