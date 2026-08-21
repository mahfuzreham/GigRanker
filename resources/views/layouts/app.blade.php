<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'GigRanker' }}</title>
    <style>
        :root{font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color-scheme:light;--bg:#f7f9fc;--surface:#fff;--surface-soft:#f8fafc;--text:#111827;--muted:#64748b;--line:#e2e8f0;--primary:#2563eb;--primary-dark:#1d4ed8;--success:#15803d;--danger:#b91c1c}
        *{box-sizing:border-box}html{background:var(--bg)}body{margin:0;background:var(--bg);color:var(--text);line-height:1.6}a{color:var(--primary);text-decoration:none}a:hover{text-decoration:underline}.wrap{width:min(1180px,92%);margin:auto}
        header{position:sticky;top:0;z-index:20;padding:14px 0;border-bottom:1px solid var(--line);background:rgba(255,255,255,.94);backdrop-filter:blur(12px);box-shadow:0 2px 12px rgba(15,23,42,.04)}.nav{display:flex;align-items:center;justify-content:space-between;gap:20px}.brand{font-weight:900;font-size:22px;color:#0f172a}.brand span{color:var(--primary)}nav{display:flex;align-items:center;gap:10px;flex-wrap:wrap}nav form{display:inline}
        main{min-height:calc(100vh - 150px);padding:42px 0}.card{background:var(--surface);border:1px solid var(--line);border-radius:16px;padding:24px;box-shadow:0 8px 28px rgba(15,23,42,.06)}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px}
        .btn{display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--primary);border-radius:10px;padding:10px 16px;background:var(--primary);color:#fff;font-weight:800;cursor:pointer;transition:.15s}.btn:hover{background:var(--primary-dark);text-decoration:none;transform:translateY(-1px)}.btn.secondary{background:#fff;color:#334155;border-color:#cbd5e1}.btn.secondary:hover{background:#f8fafc;color:#0f172a}.btn:disabled{opacity:.55;cursor:not-allowed;transform:none}
        label{display:block;font-weight:700;margin:15px 0 7px}input,textarea,select{width:100%;padding:11px 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:var(--text);outline:none}input:focus,textarea:focus,select:focus{border-color:#60a5fa;box-shadow:0 0 0 3px rgba(37,99,235,.12)}input[type=checkbox]{width:auto;accent-color:var(--primary)}textarea{min-height:130px;resize:vertical}.muted{color:var(--muted)}.alert{padding:12px 15px;border-radius:10px;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;margin-bottom:20px}.error{background:#fef2f2;border-color:#fecaca;color:#991b1b}.errors{color:#991b1b}.badge{display:inline-flex;padding:5px 10px;border-radius:999px;background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
        table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:12px;border-bottom:1px solid var(--line);vertical-align:middle}th{color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:.06em;background:#f8fafc}tr:last-child td{border-bottom:0}code{color:#1d4ed8;word-break:break-all}footer{padding:35px 0;color:#64748b;border-top:1px solid var(--line);margin-top:40px;background:#fff}@media(max-width:700px){header{position:static}.nav{align-items:flex-start;flex-direction:column}nav{gap:8px;width:100%}nav a,nav form,nav .btn{width:auto}main{padding:30px 0}.card{padding:18px}table{min-width:680px}}
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
