<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'GigRanker' }}</title>
    <style>
        :root{font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color-scheme:dark;}
        *{box-sizing:border-box} body{margin:0;background:#07101f;color:#eef5ff;line-height:1.6}
        a{color:#7dd3fc;text-decoration:none}.wrap{width:min(1100px,92%);margin:auto}
        header{padding:18px 0;border-bottom:1px solid #20304a;background:#081221}.nav{display:flex;align-items:center;justify-content:space-between;gap:20px}.brand{font-weight:800;font-size:21px}.brand span{color:#7dd3fc}
        nav{display:flex;align-items:center;gap:12px;flex-wrap:wrap} nav form{display:inline}
        main{padding:50px 0}.card{background:#0d1829;border:1px solid #223653;border-radius:16px;padding:24px}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px}
        .btn{display:inline-block;border:0;border-radius:10px;padding:12px 18px;background:#7dd3fc;color:#06101d;font-weight:800;cursor:pointer}.btn.secondary{background:#15253b;color:#dbeafe;border:1px solid #29415f}
        label{display:block;font-weight:700;margin:15px 0 7px}input,textarea,select{width:100%;padding:12px;border:1px solid #2a3d59;border-radius:10px;background:#091526;color:#eef5ff}textarea{min-height:130px;resize:vertical}
        .muted{color:#9eabc0}.alert{padding:12px 15px;border-radius:10px;background:#103126;border:1px solid #225d46;margin-bottom:20px}.error{background:#32141b;border-color:#73303c}.errors{color:#ffb4bd}
        table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:12px;border-bottom:1px solid #20304a}th{color:#9eabc0;font-size:13px;text-transform:uppercase}
        footer{padding:35px 0;color:#77869e;border-top:1px solid #1e304b;margin-top:40px}
    </style>
</head>
<body>
<header><div class="wrap nav"><a class="brand" href="{{ route('home') }}">Gig<span>Ranker</span></a><nav>
@auth
<a href="{{ route('dashboard') }}">Dashboard</a>
<a href="{{ route('projects.create') }}">New Project</a>
<form method="POST" action="{{ route('logout') }}">@csrf<button class="btn secondary" type="submit">Logout</button></form>
@else
<a href="{{ route('login') }}">Login</a>
<a class="btn" href="{{ route('register') }}">Get Started</a>
@endauth
</nav></div></header>
<main><div class="wrap">@if(session('success'))<div class="alert">{{ session('success') }}</div>@endif @if($errors->any())<div class="alert error errors"><strong>Please fix the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif @yield('content')</div></main>
<footer><div class="wrap">GigRanker — AI-powered freelance gig SEO & marketing platform.</div></footer>
</body>
</html>
