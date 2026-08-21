@extends('layouts.app')
@section('content')
<div style="max-width:1200px;margin:auto">
    <div style="display:flex;justify-content:space-between;gap:20px;align-items:center;flex-wrap:wrap;margin-bottom:24px">
        <div><span class="badge">Admin Control Center</span><h1 style="font-size:40px;margin:10px 0 4px">GigRanker Admin</h1><p class="muted" style="margin:0">Manage payments, subscriptions and production access from one protected area.</p></div>
        <a class="btn secondary" href="{{ route('admin.payments.index') }}">Payment Verification →</a>
    </div>
    <div class="grid" style="margin-bottom:22px">
        @foreach([['Users',$users],['Projects',$projects],['Active subscriptions',$activeSubscriptions],['Pending payments',$pendingPayments],['Approved payments',$approvedPayments]] as $stat)
            <div class="card"><div class="muted">{{ $stat[0] }}</div><div style="font-size:32px;font-weight:900;margin-top:6px">{{ number_format($stat[1]) }}</div></div>
        @endforeach
    </div>
    <div class="grid" style="align-items:start">
        <div class="card"><h2 style="margin-top:0">SaaS Plans</h2><div style="overflow:auto"><table><thead><tr><th>Plan</th><th>Price</th><th>AI Credits</th><th>Projects</th><th>Pages</th></tr></thead><tbody>@foreach($plans as $plan)<tr><td><strong>{{ $plan['name'] }}</strong></td><td>${{ number_format((float)$plan['price'],0) }}/mo</td><td>{{ number_format($plan['credits']) }}</td><td>{{ number_format($plan['projects']) }}</td><td>{{ number_format($plan['pages']) }}</td></tr>@endforeach</tbody></table></div></div>
        <div class="card"><h2 style="margin-top:0">Admin tools</h2><div style="display:grid;gap:10px"><a class="btn" href="{{ route('admin.payments.index') }}">💳 Review Payments</a><a class="btn secondary" href="{{ route('dashboard') }}">← User Dashboard</a><a class="btn secondary" href="{{ route('home') }}">View Website</a></div><p class="muted" style="margin-top:16px">Paid plans activate only through the existing authorized payment-review flow.</p></div>
    </div>
    <div class="card" style="margin-top:22px"><h2 style="margin-top:0">Recent Orders</h2><div style="overflow:auto"><table><thead><tr><th>User</th><th>Plan</th><th>Method</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead><tbody>@forelse($recentPayments as $payment)<tr><td>{{ $payment->user->email }}</td><td>{{ ucfirst($payment->plan) }}</td><td>{{ strtoupper($payment->method) }}</td><td>{{ $payment->currency }} {{ number_format((float)$payment->amount,2) }}</td><td><span class="badge">{{ $payment->status }}</span></td><td>{{ $payment->created_at->format('Y-m-d H:i') }}</td></tr>@empty<tr><td colspan="6" class="muted">No payments yet.</td></tr>@endforelse</tbody></table></div></div>
</div>
@endsection
