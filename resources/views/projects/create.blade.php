@extends('layouts.app')

@section('content')
<div class="hero-head">
    <div>
        <span class="eyebrow">GLOBAL GIG MARKETING</span>
        <h1>Build a website for your gig</h1>
        <p class="muted">Sell from anywhere. Choose any buyer market. GigRanker will use both profiles to create international SEO content.</p>
    </div>
</div>

<form class="card form-card" method="POST" action="{{ route('projects.store') }}">
    @csrf
    <div class="section-title"><span>01</span><div><strong>Gig details</strong><small>Tell us what you sell.</small></div></div>
    <label for="name">Project Name *</label>
    <input id="name" name="name" value="{{ old('name') }}" maxlength="120" required placeholder="My Linux Server Support Gig">

    <div class="two-col">
        <div><label for="gig_url">Fiverr Gig URL *</label><input id="gig_url" type="url" name="gig_url" value="{{ old('gig_url') }}" maxlength="2048" required placeholder="https://www.fiverr.com/..." /></div>
        <div><label for="fiverr_profile_url">Fiverr Profile URL</label><input id="fiverr_profile_url" type="url" name="fiverr_profile_url" value="{{ old('fiverr_profile_url') }}" maxlength="2048" placeholder="https://www.fiverr.com/username" /></div>
    </div>

    <label for="gig_title">Gig Title</label>
    <input id="gig_title" name="gig_title" value="{{ old('gig_title') }}" maxlength="255" placeholder="I will manage your Linux VPS server">
    <label for="gig_description">Gig Description</label>
    <textarea id="gig_description" name="gig_description" maxlength="10000" placeholder="Paste your current gig description...">{{ old('gig_description') }}</textarea>

    <div class="section-title"><span>02</span><div><strong>Seller location</strong><small>Anyone can use GigRanker. This is your location, not your buyer target.</small></div></div>
    <label for="seller_country">Where are you selling from? *</label>
    <select id="seller_country" name="seller_country" required>
        <option value="">Select your country</option>
        @foreach($sellerCountries as $code => $country)
            <option value="{{ $code }}" @selected(old('seller_country') === $code)>{{ $country }}</option>
        @endforeach
    </select>

    <div class="section-title"><span>03</span><div><strong>Buyer markets</strong><small>Select the countries or regions where you want customers.</small></div></div>
    <div class="market-grid">
        @foreach($buyerMarkets as $key => $label)
            <label class="market-option">
                <input type="checkbox" name="target_markets[]" value="{{ $key }}" @checked(in_array($key, old('target_markets', []), true))>
                <span><strong>{{ $label }}</strong><small>{{ $key === 'worldwide' ? 'Reach international buyers' : 'SEO targeting' }}</small></span>
            </label>
        @endforeach
    </div>

    <div class="two-col">
        <div><label for="target_city">Target City (optional)</label><input id="target_city" name="target_city" value="{{ old('target_city') }}" maxlength="120" placeholder="New York, London, Toronto" /></div>
        <div><label for="site_url">Marketing Website URL</label><input id="site_url" type="url" name="site_url" value="{{ old('site_url') }}" maxlength="2048" placeholder="https://yourdomain.com" /></div>
    </div>

    <div class="section-title"><span>04</span><div><strong>SEO & brand</strong><small>Optional information for stronger content.</small></div></div>
    <label for="service_category">Service Category</label><input id="service_category" name="service_category" value="{{ old('service_category') }}" maxlength="120" placeholder="Linux Server Administration">
    <label for="keywords">Main Keywords</label><textarea id="keywords" name="keywords" maxlength="2000" placeholder="cPanel support, Linux VPS, server administrator">{{ old('keywords') }}</textarea>
    <div class="two-col"><div><label for="brand_name">Brand / Name</label><input id="brand_name" name="brand_name" value="{{ old('brand_name') }}" maxlength="160" placeholder="Your brand name"></div><div><label for="github_url">GitHub / Portfolio URL</label><input id="github_url" type="url" name="github_url" value="{{ old('github_url') }}" maxlength="2048"></div></div>

    <div class="market-note"><strong>🌎 Global by design</strong><br><span>Any seller country → any buyer market. Your seller location and target customer location are kept separate.</span></div>
    <div class="form-actions"><button class="btn" type="submit">Create Marketing Project →</button><a class="btn secondary" href="{{ route('dashboard') }}">Cancel</a></div>
</form>
@endsection
