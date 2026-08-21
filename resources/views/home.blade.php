@extends('layouts.app')

@section('content')
<style>
    .hero{padding:72px 0 58px;text-align:center}
    .hero-kicker{display:inline-flex;align-items:center;gap:8px;padding:8px 13px;border:1px solid #dbe4ee;border-radius:999px;background:#f8fafc;color:#475569;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
    .hero h1{max-width:920px;margin:20px auto 18px;font-size:clamp(42px,7vw,76px);line-height:1.02;letter-spacing:-.045em;color:#0f172a}
    .hero h1 span{color:#16a34a}
    .hero-copy{max-width:760px;margin:auto;font-size:19px;color:#64748b}
    .hero-actions{display:flex;justify-content:center;gap:12px;flex-wrap:wrap;margin-top:30px}
    .trust{display:flex;justify-content:center;gap:25px;flex-wrap:wrap;margin-top:25px;color:#64748b;font-size:13px}
    .section{padding:34px 0 64px}.section-head{text-align:center;max-width:700px;margin:0 auto 28px}.section-head h2{font-size:34px;margin:0 0 8px;color:#0f172a}.section-head p{color:#64748b}
    .feature-card{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:25px;box-shadow:0 10px 30px rgba(15,23,42,.05)}.feature-icon{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;background:#f0fdf4;font-size:20px}.feature-card h3{margin:16px 0 7px;color:#0f172a}.feature-card p{margin:0;color:#64748b}
    .steps{counter-reset:step}.step{position:relative;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px}.step:before{counter-increment:step;content:counter(step);display:grid;place-items:center;width:32px;height:32px;border-radius:50%;background:#0f172a;color:#fff;font-weight:800}.step h3{color:#0f172a}.step p{color:#64748b;margin-bottom:0}
    .pricing{background:#f8fafc;border:1px solid #e2e8f0;border-radius:22px;padding:30px}.price-card{background:#fff;border:1px solid #dbe4ee;border-radius:18px;padding:25px}.price-card h3{margin:0;color:#0f172a}.price{font-size:32px;font-weight:900;color:#0f172a;margin:12px 0}.price small{font-size:13px;color:#64748b;font-weight:600}.price-card ul{padding-left:20px;color:#475569;min-height:105px}.payment-note{display:flex;gap:10px;align-items:center;margin-top:20px;padding:13px 15px;border:1px solid #bbf7d0;background:#f0fdf4;border-radius:12px;color:#166534;font-size:13px}
    .final-cta{padding:20px 0 70px}.final-box{background:#0f172a;color:#fff;border-radius:24px;padding:46px 30px;text-align:center}.final-box h2{font-size:36px;margin:0 0 10px}.final-box p{color:#cbd5e1;max-width:620px;margin:0 auto 25px}
    @media(max-width:700px){.hero{padding:48px 0 40px}.hero h1{font-size:42px}.hero-copy{font-size:17px}.section{padding-bottom:45px}}
</style>

<section class="hero">
    <span class="hero-kicker">✦ AI-powered freelance growth platform</span>
    <h1>Turn your gig into a <span>search-ready website</span>.</h1>
    <p class="hero-copy">GigRanker helps freelancers build SEO-focused pages, content and conversion paths around their services—so more people can discover and click through to their gig.</p>
    <div class="hero-actions">
        <a class="btn" href="{{ route('register') }}">Start Building Free</a>
        <a class="btn secondary" href="{{ route('billing.plans') }}">View Plans</a>
    </div>
    <div class="trust"><span>✓ No card required to start</span><span>✓ AI-assisted content</span><span>✓ Export-ready websites</span></div>
</section>

<section class="section">
    <div class="section-head"><h2>Everything you need to market one gig</h2><p>From the first project to published SEO pages, keep the workflow simple and focused.</p></div>
    <div class="grid">
        <div class="feature-card"><div class="feature-icon">🎯</div><h3>Gig-focused campaigns</h3><p>Build a dedicated marketing project around your freelance service and target audience.</p></div>
        <div class="feature-card"><div class="feature-icon">🔎</div><h3>SEO-ready pages</h3><p>Generate structured titles, descriptions, schema, FAQs, canonical URLs and internal links.</p></div>
        <div class="feature-card"><div class="feature-icon">🤖</div><h3>AI-assisted generation</h3><p>Use AI to accelerate content creation while GigRanker controls templates and output.</p></div>
        <div class="feature-card"><div class="feature-icon">📈</div><h3>Click tracking</h3><p>Track outbound visitors who click from your marketing pages back to your freelance gig.</p></div>
        <div class="feature-card"><div class="feature-icon">📦</div><h3>Export your site</h3><p>Preview your generated pages and export the static website when your project is ready.</p></div>
        <div class="feature-card"><div class="feature-icon">🛡️</div><h3>Protected billing</h3><p>Paid plans use administrator-reviewed payment verification before subscription activation.</p></div>
    </div>
</section>

<section class="section">
    <div class="section-head"><h2>Simple workflow</h2><p>Create, generate and publish without a complicated marketing stack.</p></div>
    <div class="grid steps">
        <div class="step"><h3>Create a project</h3><p>Add your gig URL, title, service and target market.</p></div>
        <div class="step"><h3>Generate SEO pages</h3><p>Use your available AI credits to create targeted marketing pages.</p></div>
        <div class="step"><h3>Preview & export</h3><p>Review the content, preview your site and export when ready.</p></div>
    </div>
</section>

<section class="section">
    <div class="pricing">
        <div class="section-head"><h2>Start free. Upgrade when you need more.</h2><p>Choose a plan from the billing area when your project grows.</p></div>
        <div class="grid">
            <div class="price-card"><h3>Free</h3><div class="price">$0 <small>/ month</small></div><ul><li>Starter AI allowance</li><li>Project-based workflow</li><li>SEO page generation</li></ul><a class="btn secondary" href="{{ route('register') }}" style="width:100%">Get Started</a></div>
            <div class="price-card"><h3>Starter</h3><div class="price">Paid <small>/ month</small></div><ul><li>More AI credits</li><li>More project capacity</li><li>Extended page limits</li></ul><a class="btn" href="{{ route('billing.plans') }}" style="width:100%">View Starter</a></div>
            <div class="price-card"><h3>Pro & Agency</h3><div class="price">Scale <small>with your work</small></div><ul><li>Higher AI allowances</li><li>More projects & pages</li><li>Built for growing campaigns</li></ul><a class="btn secondary" href="{{ route('billing.plans') }}" style="width:100%">Compare Plans</a></div>
        </div>
        <div class="payment-note">₮ <strong>USDT payments supported</strong> — pay paid plans using BEP20 USDT and submit the blockchain TXID for administrator verification.</div>
    </div>
</section>

<section class="final-cta"><div class="final-box"><h2>Ready to grow your gig?</h2><p>Create your first GigRanker project and turn your freelance service into a focused SEO marketing asset.</p><a class="btn" href="{{ route('register') }}">Create Your Free Project</a></div></section>
@endsection
