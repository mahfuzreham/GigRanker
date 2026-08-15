@extends('layouts.app')

@section('content')
<div style="max-width:1100px;margin:0 auto">
    <div style="text-align:center;margin-bottom:38px">
        <span class="badge">GigRanker Plans</span>
        <h1 style="font-size:clamp(34px,5vw,52px);line-height:1.08;margin:14px 0 10px;letter-spacing:-1.5px">Choose the plan that fits your growth.</h1>
        <p class="muted" style="max-width:650px;margin:0 auto;font-size:17px">More AI credits, more marketing pages, and more projects to help turn your freelance gig into a searchable marketing asset.</p>
    </div>

    @if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
    @if(session('info'))<div class="alert">{{ session('info') }}</div>@endif
    @if($errors->any())<div class="alert error errors">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(235px,1fr));gap:18px">
        @foreach($plans as $key => $plan)
            <div class="card" style="position:relative;overflow:hidden;display:flex;flex-direction:column;{{ $key === 'pro' ? 'border-color:rgba(139,92,246,.65);box-shadow:0 20px 65px rgba(99,102,241,.18)' : '' }}">
                @if($key === 'pro')<div class="badge" style="position:absolute;right:18px;top:18px">Popular</div>@endif
                @if($key === $currentPlan)<div class="badge" style="margin-bottom:14px">Current Plan</div>@endif
                <div class="muted" style="font-weight:700">{{ $plan['name'] }}</div>
                <div style="font-size:42px;font-weight:900;letter-spacing:-1px;margin:8px 0">${{ number_format($plan['price'], 0) }}<span class="muted" style="font-size:14px;font-weight:500"> / month</span></div>
                <div class="muted" style="font-size:14px;margin-bottom:20px">Built for {{ $plan['projects'] == 1 ? 'getting started' : ($key === 'agency' ? 'teams & agencies' : 'growing freelancers') }}.</div>
                <ul style="list-style:none;padding:0;margin:0 0 24px;display:grid;gap:10px;flex:1">
                    <li>✓ <strong>{{ number_format($plan['credits']) }}</strong> AI credits</li>
                    <li>✓ <strong>{{ number_format($plan['projects']) }}</strong> project{{ $plan['projects'] > 1 ? 's' : '' }}</li>
                    <li>✓ Up to <strong>{{ number_format($plan['pages']) }}</strong> SEO pages</li>
                    <li>✓ SEO-ready marketing output</li>
                </ul>
                <form method="POST" action="{{ route('billing.select') }}">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $key }}">
                    <button class="btn" type="submit" style="width:100%">{{ $key === $currentPlan ? 'Current Plan' : ($key === 'free' ? 'Start Free' : 'Continue to Payment') }}</button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="card" style="margin-top:22px;text-align:center;background:linear-gradient(135deg,rgba(34,211,238,.06),rgba(139,92,246,.08))">
        <strong>Secure payment flow</strong>
        <div class="muted" style="margin-top:5px">Paid plans activate only after a verified payment. bKash and BEP20 verification will be handled server-side.</div>
    </div>
</div>
@endsection
