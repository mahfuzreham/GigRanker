@extends('layouts.admin')
@section('content')
<div class="page-head"><div><div class="eyebrow">Overview</div><h1>Dashboard</h1><p>Monitor GigRanker users, projects, subscriptions and payments.</p></div><div class="actions"><a class="btn" href="{{ route('admin.payments.index') }}">Review Payments</a><a class="btn secondary" href="{{ route('admin.settings') }}">Settings</a></div></div>
<div class="grid" style="margin-bottom:18px">
@foreach([['Users',$users,'◉'],['Projects',$projects,'▦'],['Active subscriptions',$activeSubscriptions,'✓'],['Pending payments',$pendingPayments,'$'],['Approved payments',$approvedPayments,'↗']] as $stat)
<div class="stat"><div class="stat-icon">{{ $stat[2] }}</div><div class="stat-label">{{ $stat[0] }}</div><div class="stat-value">{{ number_format($stat[1]) }}</div></div>
@endforeach
</div>
<div class="grid">
<div class="panel span-8"><div class="panel-head"><h2>SaaS Plans</h2><span class="badge">Pricing</span></div><div style="overflow:auto"><table><thead><tr><th>Plan</th><th>Price</th><th>AI Credits</th><th>Projects</th><th>Pages</th></tr></thead><tbody>@foreach($plans as $plan)<tr><td><strong>{{ $plan['name'] }}</strong></td><td>USDT {{ number_format((float)$plan['price'],0) }}/mo</td><td>{{ number_format($plan['credits']) }}</td><td>{{ number_format($plan['projects']) }}</td><td>{{ number_format($plan['pages']) }}</td></tr>@endforeach</tbody></table></div></div>
<div class="panel span-4"><div class="panel-head"><h2>Quick actions</h2></div><div style="display:grid;gap:9px"><a class="btn" href="{{ route('admin.payments.index') }}">Payment Verification</a><a class="btn secondary" href="{{ route('admin.settings') }}">AI & USDT Settings</a><a class="btn secondary" href="{{ route('dashboard') }}">User Dashboard</a><a class="btn secondary" href="{{ route('home') }}">View Website</a></div></div>
<div class="panel span-12"><div class="panel-head"><h2>Recent Orders</h2><span class="badge">Latest</span></div><div style="overflow:auto"><table><thead><tr><th>User</th><th>Plan</th><th>Method</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead><tbody>@forelse($recentPayments as $payment)<tr><td>{{ $payment->user->email }}</td><td>{{ ucfirst($payment->plan) }}</td><td>{{ strtoupper($payment->method) }}</td><td>{{ $payment->currency }} {{ number_format((float)$payment->amount,2) }}</td><td><span class="badge {{ $payment->status === 'approved' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">{{ $payment->status }}</span></td><td>{{ $payment->created_at->format('Y-m-d H:i') }}</td></tr>@empty<tr><td colspan="6" class="muted">No payments yet.</td></tr>@endforelse</tbody></table></div></div>
</div>
@endsection
