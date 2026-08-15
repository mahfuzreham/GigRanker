@extends('layouts.app')

@section('content')
<div style="display:flex;justify-content:space-between;gap:16px;align-items:center;flex-wrap:wrap;margin-bottom:24px">
    <div>
        <span class="badge">Admin Console</span>
        <h1 style="font-size:38px;line-height:1.1;margin:10px 0 6px">GigRanker Control Center</h1>
        <p class="muted" style="margin:0">Monitor users, projects, subscriptions and payment verification from one secure dashboard.</p>
    </div>
    <a class="btn" href="{{ route('admin.payments') }}">Review Payments</a>
</div>

<div class="grid" style="margin-bottom:24px">
    <div class="card"><div class="muted">Users</div><div style="font-size:34px;font-weight:900;margin-top:6px">{{ number_format($users) }}</div></div>
    <div class="card"><div class="muted">Projects</div><div style="font-size:34px;font-weight:900;margin-top:6px">{{ number_format($projects) }}</div></div>
    <div class="card"><div class="muted">Active subscriptions</div><div style="font-size:34px;font-weight:900;margin-top:6px">{{ number_format($activeSubscriptions) }}</div></div>
    <div class="card" style="border-color:rgba(251,191,36,.35)"><div class="muted">Pending payments</div><div style="font-size:34px;font-weight:900;margin-top:6px">{{ number_format($pendingPayments) }}</div><a href="{{ route('admin.payments') }}" class="muted">Review now →</a></div>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px">
        <div><h2 style="margin:0">Recent payments</h2><p class="muted" style="margin:4px 0">{{ number_format($verifiedPayments) }} verified payments total.</p></div>
        <a href="{{ route('admin.payments') }}">View all</a>
    </div>
    <div style="overflow:auto">
        <table>
            <thead><tr><th>User</th><th>Plan</th><th>Method</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($recentPayments as $payment)
                <tr>
                    <td>{{ $payment->user->email }}</td>
                    <td>{{ ucfirst($payment->plan) }}</td>
                    <td>{{ strtoupper($payment->method) }}</td>
                    <td>{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                    <td><span class="badge">{{ $payment->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No payments yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
