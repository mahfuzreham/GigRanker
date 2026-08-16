@extends('layouts.app')

@section('content')
<div style="max-width:1050px;margin:0 auto">
    <div style="margin-bottom:25px">
        <h1 style="margin:0">Choose Your Plan</h1>
        <p class="muted">Upgrade your GigRanker AI credits and marketing capacity.</p>
    </div>

    @if(session('success'))
        <div class="card" style="margin-bottom:18px">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="card" style="margin-bottom:18px">{{ session('info') }}</div>
    @endif
    @if($errors->any())
        <div class="card" style="margin-bottom:18px">
            @foreach($errors->all() as $error)<p style="margin:0 0 6px">{{ $error }}</p>@endforeach
        </div>
    @endif

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px">
        @foreach($plans as $key => $plan)
            <div class="card">
                @if($key === $currentPlan)<span style="font-size:12px">CURRENT PLAN</span>@endif
                <h2>{{ $plan['name'] }}</h2>
                <div style="font-size:32px;font-weight:800">${{ number_format($plan['price'], 0) }}<span class="muted" style="font-size:14px">/month</span></div>
                <ul>
                    <li>{{ number_format($plan['credits']) }} AI credits</li>
                    <li>{{ number_format($plan['projects']) }} projects</li>
                    <li>Up to {{ number_format($plan['pages']) }} SEO pages</li>
                </ul>
                <form method="POST" action="{{ route('billing.select') }}">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $key }}">
                    <button class="btn" type="submit" style="width:100%">
                        {{ $key === $currentPlan ? 'Current Plan' : ($key === 'free' ? 'Select Free' : 'Continue to Payment') }}
                    </button>
                </form>
            </div>
        @endforeach
    </div>

    <p class="muted" style="margin-top:22px">Paid plans are not activated until a verified payment is recorded.</p>
</div>
@endsection
