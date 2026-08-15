@extends('layouts.app')

@section('content')
<section style="text-align:center;padding:55px 0 35px">
    <p style="color:#7dd3fc;font-weight:800">AI-POWERED GIG MARKETING</p>
    <h1 style="font-size:clamp(40px,7vw,68px);line-height:1.05;margin:15px 0">Turn Your Freelance Gig Into an SEO Marketing Website.</h1>
    <p class="muted" style="max-width:760px;margin:auto;font-size:18px">Create SEO-ready service pages, blog content, internal links, schema markup and conversion-focused calls to action that send interested visitors to your freelance gig.</p>
    <p style="margin-top:28px"><a class="btn" href="{{ route('projects.create') }}">Create Marketing Project</a></p>
</section>
<section class="grid">
    <div class="card"><h3>🎯 Gig-focused</h3><p class="muted">The product is designed around marketing your freelance service and driving qualified visitors to your gig.</p></div>
    <div class="card"><h3>🔎 SEO-ready</h3><p class="muted">Generate structured content with titles, descriptions, canonical URLs, schema, FAQ and internal linking.</p></div>
    <div class="card"><h3>🤖 AI-assisted</h3><p class="muted">AI providers will generate content data while GigRanker controls the final templates and output.</p></div>
</section>
@endsection
