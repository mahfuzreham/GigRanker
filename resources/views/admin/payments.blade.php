@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;gap:15px;align-items:center;flex-wrap:wrap;margin-bottom:20px">
        <div><span class="badge">Admin</span><h1 style="margin:8px 0 4px">Payment & Order History</h1><p class="muted" style="margin:0">Search and review every bKash and BEP20 payment.</p></div>
    </div>
    @if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px">
        <input name="q" value="{{ $filters['q'] }}" placeholder="Email, order ID or transaction ID" style="min-width:260px">
        <select name="status"><option value="">All statuses</option><option value="pending" @selected($filters['status']==='pending')>Pending</option><option value="verified" @selected($filters['status']==='verified')>Verified</option><option value="rejected" @selected($filters['status']==='rejected')>Rejected</option></select>
        <select name="method"><option value="">All methods</option><option value="bkash" @selected($filters['method']==='bkash')>bKash</option><option value="bep20" @selected($filters['method']==='bep20')>BEP20</option></select>
        <button class="btn" type="submit">Search</button>
        <a class="btn secondary" href="{{ route('admin.payments') }}">Reset</a>
    </form>

    <div style="overflow:auto"><table>
        <thead><tr><th>User</th><th>Plan</th><th>Method</th><th>Amount</th><th>Reference</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
        <tbody>
        @forelse($payments as $payment)
        <tr>
            <td>{{ $payment->user->email }}</td><td>{{ ucfirst($payment->plan) }}</td><td>{{ strtoupper($payment->method) }}</td>
            <td>{{ $payment->currency }} {{ number_format((float)$payment->amount,2) }}</td><td><code>{{ $payment->transaction_reference }}</code></td>
            <td><span class="badge">{{ $payment->status }}</span></td><td>{{ optional($payment->created_at)->format('Y-m-d H:i') }}</td>
            <td>@if($payment->status==='pending')<div style="display:flex;gap:8px"><form method="POST" action="{{ route('admin.payments.verify',$payment) }}">@csrf<button class="btn" type="submit">Verify</button></form><form method="POST" action="{{ route('admin.payments.reject',$payment) }}">@csrf<button class="btn secondary" type="submit">Reject</button></form></div>@else<span class="muted">Processed</span>@endif</td>
        </tr>
        @empty<tr><td colspan="8" class="muted">No matching payments.</td></tr>@endforelse
        </tbody>
    </table></div>
    <div style="margin-top:20px">{{ $payments->links() }}</div>
</div>
@endsection
