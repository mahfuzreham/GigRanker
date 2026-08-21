<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'GigRanker' }}</title>
    <style>
        :root{font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color-scheme:dark;--bg:#07101f;--muted:#9eabc0;--accent:#7dd3fc}
        *{box-sizing:border-box}body{margin:0;background:radial-gradient(circle at 10% -10%,#172554 0,transparent 35%),var(--bg);color:#eef5ff;line-height:1.6}a{color:#7dd3fc;text-decoration:none}.wrap{width:min(1120px,92%);margin:auto}
        header{position:sticky;top:0;z-index:20;padding:16px 0;border-bottom:1px solid #20304a;background:rgba(8,18,33,.94);backdrop-filter:blur(12px)}.nav{display:flex;align-items:center;justify-content:space-between;gap:20px}.brand{font-weight:900;font-size:22px;color:#fff}.brand span{color:#7dd3fc}nav{display:flex;align-items:center;gap:12px;flex-wrap:wrap}nav form{display:inline}
        main{min-height:calc(100vh - 150px);padding:50px 0}.card{background:linear-gradient(145deg,rgba(13,24,41,.98),rgba(8,16,29,.98));border:1px solid #223653;border-radius:18px;padding:24px;box-shadow:0 18px 45px rgba(0,0,0,.18)}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px}
        .btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:10px;padding:12px 18px;background:#7dd3fc;color:#06101d;font-weight:800;cursor:pointer}.btn.secondary{background:#15253b;color:#dbeafe;border:1px solid #29415f}.btn:hover{filter:brightness(1.08)}
        label{display:block;font-weight:700;margin:15px 0 7px}input,textarea,select{width:100%;padding:12px;border:1px solid #2a3d59;border-radius:10px;background:#091526;color:#eef5ff}textarea{min-height:130px;resize:vertical}.muted{color:var(--muted)}.alert{padding:12px 15px;border-radius:10px;background:#103126;border:1px solid #225d46;margin-bottom:20px}.error{background:#32141b;border-color:#73303c}.errors{color:#ffb4bd}.badge{display:inline-flex;padding:5px 10px;border-radius:999px;background:#172554;border:1px solid #334155;color:#bae6fd;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
        table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:12px;border-bottom:1px solid #20304a}th{color:#9eabc0;font-size:12px;text-transform:uppercase;letter-spacing:.06em}code{color:#bae6fd;word-break:break-all}footer{padding:35px 0;color:#77869e;border-top:1px solid #1e304b;margin-top:40px}@media(max-width:700px){header{position:static}.nav{align-items:flex-start;flex-direction:column}nav{gap:9px}main{padding:35px 0}.card{padding:20px}}
    </style>
</head>
<body>
<header><div class="wrap nav"><a class="brand" href="{{ route('home') }}">Gig<span>Ranker</span></a><nav>
@auth
<a href="{{ route('dashboard') }}">Dashboard</a><a href="{{ route('projects.create') }}">New Project</a>
@if(in_array(strtolower((string) auth()->user()->email), config('gigranker.admin.emails', []), true))<a class="btn secondary" href="{{ route('admin.dashboard') }}">Admin</a>@endif
<form method="POST" action="{{ route('logout') }}">@csrf<button class="btn secondary" type="submit">Logout</button></form>
@else
<a href="{{ route('login') }}">Login</a><a href="{{ route('admin.login') }}">Admin Login</a><a class="btn" href="{{ route('register') }}">Get Started</a>
@endauth
</nav></div></header>
<main><div class="wrap">@if(session('success'))<div class="alert">{{ session('success') }}</div>@endif @if($errors->any())<div class="alert error errors"><strong>Please fix the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif @yield('content')</div></main>
<footer><div class="wrap">GigRanker — AI-powered freelance gig SEO & marketing platform.</div></footer>
</body>
</html>
