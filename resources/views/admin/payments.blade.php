@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;gap:15px;align-items:center;flex-wrap:wrap;margin-bottom:20px">
        <div><span class="badge">Admin</span><h1 style="margin:8px 0 4px">Payment Verification</h1><p class="muted" style="margin:0">Review pending bKash and BEP20 submissions before activating subscriptions.</p></div>
    </div>
    @if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
    <div style="overflow:auto">
        <table>
            <thead><tr><th>User</th><th>Plan</th><th>Method</th><th>Amount</th><th>Reference</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->user->email }}</td><td>{{ ucfirst($payment->plan) }}</td><td>{{ strtoupper($payment->method) }}</td>
                    <td>{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td><td><code>{{ $payment->transaction_reference }}</code></td>
                    <td><span class="badge">{{ $payment->status }}</span></td>
                    <td>@if($payment->status === 'pending')<div style="display:flex;gap:8px"><form method="POST" action="{{ route('admin.payments.verify',$payment) }}">@csrf<button class="btn" type="submit">Verify</button></form><form method="POST" action="{{ route('admin.payments.reject',$payment) }}">@csrf<button class="btn secondary" type="submit">Reject</button></form></div>@else<span class="muted">Processed</span>@endif</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">No payment submissions yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:20px">{{ $payments->links() }}</div>
</div>
@endsection
