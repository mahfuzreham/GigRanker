<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'GigRanker' }}</title>
    <style>
        :root{font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color-scheme:dark;--bg:#060b16;--muted:#94a3b8;--accent:#8b5cf6;--accent2:#22d3ee}
        *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:radial-gradient(circle at 15% -10%,#172554 0,transparent 35%),radial-gradient(circle at 90% 0,#164e63 0,transparent 30%),var(--bg);color:#f5f7fb;line-height:1.65}a{color:#a5b4fc;text-decoration:none}a:hover{color:#c4b5fd}.wrap{width:min(1120px,92%);margin:auto}
        header{position:sticky;top:0;z-index:20;padding:15px 0;border-bottom:1px solid rgba(148,163,184,.12);background:rgba(6,11,22,.84);backdrop-filter:blur(18px)}.nav{display:flex;align-items:center;justify-content:space-between;gap:20px}.brand{font-weight:900;font-size:22px;color:#fff}.brand span{background:linear-gradient(90deg,var(--accent2),#a78bfa);-webkit-background-clip:text;background-clip:text;color:transparent}nav{display:flex;align-items:center;gap:16px;flex-wrap:wrap}nav form{display:inline}
        main{min-height:calc(100vh - 150px);padding:54px 0}.card{background:linear-gradient(145deg,rgba(16,27,47,.95),rgba(8,15,29,.95));border:1px solid rgba(100,116,139,.25);border-radius:20px;padding:26px;box-shadow:0 18px 50px rgba(0,0,0,.22)}.card:hover{border-color:rgba(139,92,246,.38)}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px}.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:1px solid transparent;border-radius:12px;padding:12px 19px;background:linear-gradient(135deg,var(--accent),#6366f1);color:#fff;font-weight:800;cursor:pointer;box-shadow:0 8px 25px rgba(99,102,241,.22);transition:.2s}.btn:hover{transform:translateY(-1px);color:#fff;filter:brightness(1.08)}.btn.secondary{background:#111d32;color:#dbeafe;border-color:#2b3d5b;box-shadow:none}
        label{display:block;font-weight:750;margin:16px 0 7px}input,textarea,select{width:100%;padding:13px 14px;border:1px solid #293c5a;border-radius:12px;background:#07111f;color:#eef5ff;outline:none;transition:.2s}input:focus,textarea:focus,select:focus{border-color:#8b5cf6;box-shadow:0 0 0 3px rgba(139,92,246,.13)}textarea{min-height:130px;resize:vertical}.muted{color:var(--muted)}.alert{padding:13px 16px;border-radius:12px;background:rgba(16,185,129,.10);border:1px solid rgba(52,211,153,.28);margin-bottom:20px}.error{background:rgba(239,68,68,.10);border-color:rgba(248,113,113,.3)}.errors{color:#fecaca}.badge,.eyebrow{display:inline-flex;padding:5px 10px;border-radius:999px;background:rgba(139,92,246,.13);border:1px solid rgba(139,92,246,.28);color:#c4b5fd;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.08em}
        .hero-head{margin:0 0 28px}.hero-head h1{font-size:clamp(30px,5vw,48px);line-height:1.08;margin:10px 0}.form-card{max-width:980px;margin:auto}.two-col{display:grid;grid-template-columns:1fr 1fr;gap:18px}.section-title{display:flex;align-items:center;gap:14px;margin:30px 0 5px;padding-top:6px}.section-title>span{display:grid;place-items:center;width:34px;height:34px;border-radius:10px;background:rgba(139,92,246,.15);color:#c4b5fd;font-weight:900}.section-title strong{display:block;font-size:18px}.section-title small{display:block;color:#718096}.market-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:10px}.market-option{margin:0;display:flex;align-items:center;gap:12px;padding:14px;border:1px solid #293c5a;border-radius:14px;background:#07111f;cursor:pointer;transition:.2s}.market-option:hover{border-color:#8b5cf6;transform:translateY(-1px)}.market-option input{width:auto;margin:0;accent-color:#8b5cf6}.market-option span{display:block}.market-option strong{display:block}.market-option small{color:#718096}.market-note{margin-top:24px;padding:16px;border-radius:14px;background:linear-gradient(135deg,rgba(34,211,238,.08),rgba(139,92,246,.10));border:1px solid rgba(139,92,246,.24);color:#dbeafe}.market-note span{color:#94a3b8}.form-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:26px}
        table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:13px;border-bottom:1px solid rgba(148,163,184,.12)}th{color:#94a3b8;font-size:12px;text-transform:uppercase;letter-spacing:.08em}footer{padding:38px 0;color:#718096;border-top:1px solid rgba(148,163,184,.12);margin-top:50px}@media(max-width:700px){header{position:static}.nav{align-items:flex-start;flex-direction:column}nav{gap:10px}.card{padding:20px}main{padding:35px 0}.two-col,.market-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<header><div class="wrap nav"><a class="brand" href="{{ route('home') }}">Gig<span>Ranker</span></a><nav>
@auth
<a href="{{ route('dashboard') }}">Dashboard</a><a href="{{ route('billing.plans') }}">Plans</a><a href="{{ route('projects.create') }}">New Project</a>
@if(auth()->user()->is_admin)<a href="{{ route('admin.dashboard') }}">Admin</a>@endif
<form method="POST" action="{{ route('logout') }}">@csrf<button class="btn secondary" type="submit">Logout</button></form>
@else
<a href="{{ route('login') }}">Login</a><a class="btn" href="{{ route('register') }}">Get Started</a>
@endauth
</nav></div></header>
<main><div class="wrap">@if(session('success'))<div class="alert">{{ session('success') }}</div>@endif @if($errors->any())<div class="alert error errors"><strong>Please fix the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif @yield('content')</div></main>
<footer><div class="wrap">GigRanker <span class="muted">— Built for freelancers worldwide. Reach buyers worldwide.</span></div></footer>
</body>
</html>
