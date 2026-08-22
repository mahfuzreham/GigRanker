<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'GigRanker' }}</title>
    <style>
        :root{--brand:#465fff;--brand-soft:#ecf3ff;--text:#101828;--muted:#667085;--line:#e4e7ec;--bg:#f9fafb}
        *{box-sizing:border-box}html,body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}
        a{text-decoration:none;color:inherit}.sidebar{position:fixed;inset:0 auto 0 0;width:248px;background:#fff;border-right:1px solid var(--line);padding:25px 16px;z-index:50}
        .logo{display:block;font-size:22px;font-weight:900;padding:0 12px 28px}.logo span{color:var(--brand)}.menu-title{font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#98a2b3;padding:13px 12px 8px}
        .menu{display:grid;gap:3px}.menu a{display:flex;align-items:center;gap:11px;padding:10px 12px;border-radius:8px;color:#475467;font-size:14px;font-weight:600}.menu a:hover,.menu a.active{background:var(--brand-soft);color:var(--brand)}.icon{width:20px;text-align:center}
        .main{margin-left:248px;min-height:100vh}.topbar{height:70px;background:#fff;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 30px;position:sticky;top:0;z-index:30}
        .top-actions{display:flex;align-items:center;gap:9px}.avatar{width:36px;height:36px;border-radius:50%;display:grid;place-items:center;background:var(--brand-soft);color:var(--brand);font-weight:800}.content{padding:30px;max-width:1320px;margin:auto}
        .card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:22px;box-shadow:0 1px 2px rgba(16,24,40,.03)}.grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:18px}
        .btn{display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--brand);border-radius:8px;padding:9px 14px;background:var(--brand);color:#fff;font-size:13px;font-weight:700;cursor:pointer}.btn.secondary{background:#fff;color:#344054;border-color:#d0d5dd}
        .muted{color:var(--muted)}.badge{display:inline-flex;padding:4px 8px;border-radius:999px;background:var(--brand-soft);color:var(--brand);font-size:11px;font-weight:800;text-transform:capitalize}
        label{display:block;font-size:13px;font-weight:700;margin:0 0 7px}input,textarea,select{width:100%;border:1px solid #d0d5dd;border-radius:8px;padding:10px 12px;background:#fff;color:var(--text);outline:none}textarea{min-height:130px;resize:vertical}
        table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:13px 10px;border-bottom:1px solid #f0f2f5;font-size:13px;vertical-align:middle}th{font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#98a2b3;background:#fcfcfd}
        .alert{padding:12px 14px;border:1px solid #abefc6;background:#ecfdf3;color:#067647;border-radius:8px;margin-bottom:18px}.error{border-color:#fecdca;background:#fef3f2;color:#b42318}.mobile-toggle{display:none;border:0;background:#fff;font-size:22px}.overlay{display:none}
        @media(max-width:760px){.sidebar{transform:translateX(-100%);transition:.2s}.sidebar.open{transform:translateX(0)}.main{margin-left:0}.topbar{padding:0 16px}.mobile-toggle{display:block}.content{padding:20px 15px}.card{padding:18px}.overlay.open{display:block;position:fixed;inset:0;background:rgba(16,24,40,.35);z-index:40}table{min-width:720px}}
    </style>
</head>
<body>
@php
    $currentUser = auth()->user();
    $isAuthenticated = $currentUser !== null;
    $isAdmin = $isAuthenticated && in_array(strtolower((string) $currentUser->email), config('gigranker.admin.emails', []), true);
@endphp
<div>
    @if($isAuthenticated)
        <aside class="sidebar" id="userSidebar">
            <a class="logo" href="{{ route('dashboard') }}">Gig<span>Ranker</span></a>
            <div class="menu-title">Workspace</div>
            <nav class="menu">
                <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="icon">▦</span>Dashboard</a>
                <a class="{{ request()->routeIs('projects.create') ? 'active' : '' }}" href="{{ route('projects.create') }}"><span class="icon">＋</span>New Project</a>
                <a href="{{ route('billing.plans') }}"><span class="icon">◈</span>Plans &amp; Billing</a>
            </nav>
            @if($isAdmin)
                <div class="menu-title">Administration</div>
                <nav class="menu"><a href="{{ route('admin.dashboard') }}"><span class="icon">⚙</span>Admin Panel</a></nav>
            @endif
            <div style="position:absolute;left:16px;right:16px;bottom:20px;border-top:1px solid var(--line);padding:15px 12px">
                <div style="font-size:12px;font-weight:700">{{ $currentUser->name ?: 'Workspace' }}</div>
                <div class="muted" style="font-size:11px;word-break:break-all">{{ $currentUser->email }}</div>
                <form method="POST" action="{{ route('logout') }}" style="margin-top:10px">
                    @csrf
                    <button class="btn secondary" style="width:100%" type="submit">Logout</button>
                </form>
            </div>
        </aside>
        <div class="overlay" id="userOverlay" onclick="toggleUserSidebar()"></div>
    @endif

    <section class="main">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:12px">
                @if($isAuthenticated)<button class="mobile-toggle" onclick="toggleUserSidebar()" type="button">☰</button>@endif
                <div>
                    <strong>{{ $isAuthenticated ? 'Workspace' : 'GigRanker' }}</strong>
                    <div class="muted" style="font-size:12px">{{ $isAuthenticated ? 'Manage your projects &amp; growth' : 'AI-powered gig marketing' }}</div>
                </div>
            </div>
            <div class="top-actions">
                @if($isAuthenticated)
                    <a class="btn secondary" href="{{ route('home') }}">Website</a>
                    <div class="avatar">{{ strtoupper(substr($currentUser->name ?: $currentUser->email,0,1)) }}</div>
                @else
                    <a href="{{ route('login') }}">Login</a>
                    <a class="btn" href="{{ route('register') }}">Get Started</a>
                @endif
            </div>
        </header>
        <main class="content">
            @if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
            @if($errors->any())
                <div class="alert error">
                    <strong>Please fix the following:</strong>
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
    </section>
</div>
<script>function toggleUserSidebar(){document.getElementById('userSidebar')?.classList.toggle('open');document.getElementById('userOverlay')?.classList.toggle('open')}</script>
</body>
</html>
