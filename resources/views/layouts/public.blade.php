<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'GigRanker' }}</title>
    <style>
        :root{--brand:#465fff;--text:#101828;--muted:#667085;--line:#eaecf0}*{box-sizing:border-box}html,body{margin:0;background:#fff;color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}a{text-decoration:none;color:inherit}.btn{display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--brand);border-radius:9px;padding:10px 16px;background:var(--brand);color:#fff;font-size:13px;font-weight:750;cursor:pointer}.btn.secondary{background:#fff;color:#344054;border-color:#d0d5dd}.badge{display:inline-flex;padding:4px 8px;border-radius:999px;background:#eef4ff;color:var(--brand);font-size:11px;font-weight:800;text-transform:capitalize}
    </style>
    @stack('head')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
