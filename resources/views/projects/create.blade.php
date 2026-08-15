@extends('layouts.app')

@section('content')
<div style="max-width:850px;margin:auto">
    <h1>Create Marketing Project</h1>
    <p class="muted">Add the information GigRanker will use to build your SEO marketing strategy and website.</p>

    <form class="card" method="POST" action="{{ route('projects.store') }}">
        @csrf

        <label for="name">Project Name *</label>
        <input id="name" name="name" value="{{ old('name') }}" maxlength="120" required placeholder="My cPanel Server Support Gig">

        <label for="gig_url">Fiverr Gig URL *</label>
        <input id="gig_url" type="url" name="gig_url" value="{{ old('gig_url') }}" maxlength="2048" required placeholder="https://www.fiverr.com/...">

        <label for="site_url">Your Marketing Website URL</label>
        <input id="site_url" type="url" name="site_url" value="{{ old('site_url') }}" maxlength="2048" placeholder="https://example.com">
        <p class="muted">Optional now. Add the final domain before publishing so canonical URLs and sitemap.xml use your own website.</p>

        <label for="gig_title">Gig Title</label>
        <input id="gig_title" name="gig_title" value="{{ old('gig_title') }}" maxlength="255" placeholder="I will manage your Linux VPS server">

        <label for="gig_description">Gig Description</label>
        <textarea id="gig_description" name="gig_description" maxlength="10000" placeholder="Paste the current gig description here...">{{ old('gig_description') }}</textarea>

        <label for="service_category">Service Category</label>
        <input id="service_category" name="service_category" value="{{ old('service_category') }}" maxlength="120" placeholder="Linux Server Administration">

        <div class="grid">
            <div>
                <label for="target_country">Target Country</label>
                <input id="target_country" name="target_country" value="{{ old('target_country') }}" maxlength="120" placeholder="United States">
            </div>
            <div>
                <label for="target_city">Target City (optional)</label>
                <input id="target_city" name="target_city" value="{{ old('target_city') }}" maxlength="120" placeholder="New York">
            </div>
        </div>

        <label for="keywords">Main Keywords</label>
        <textarea id="keywords" name="keywords" maxlength="2000" placeholder="cPanel support, Linux VPS, server administrator">{{ old('keywords') }}</textarea>
        <p class="muted">Separate keywords with commas or new lines.</p>

        <label for="brand_name">Brand / Name</label>
        <input id="brand_name" name="brand_name" value="{{ old('brand_name') }}" maxlength="160" placeholder="Your brand name">

        <label for="fiverr_profile_url">Fiverr Profile URL</label>
        <input id="fiverr_profile_url" type="url" name="fiverr_profile_url" value="{{ old('fiverr_profile_url') }}" maxlength="2048">

        <label for="github_url">GitHub / Portfolio URL</label>
        <input id="github_url" type="url" name="github_url" value="{{ old('github_url') }}" maxlength="2048">

        <div style="margin-top:25px;display:flex;gap:12px;flex-wrap:wrap">
            <button class="btn" type="submit">Save Project</button>
            <a class="btn secondary" href="{{ route('dashboard') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
