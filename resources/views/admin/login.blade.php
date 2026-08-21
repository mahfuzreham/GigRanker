@extends('layouts.app')
@section('content')
<div style="max-width:480px;margin:20px auto">
    <div class="card" style="padding:32px">
        <div style="text-align:center;margin-bottom:24px">
            <span class="badge">Secure admin area</span>
            <h1 style="margin:12px 0 5px">Admin Sign In</h1>
            <p class="muted" style="margin:0">Sign in to manage GigRanker operations.</p>
        </div>
        @if($errors->any())
            <div class="alert error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        @endif
        <form method="POST" action="{{ route('admin.login.store') }}">
            @csrf
            <label for="email">Admin email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
            <label style="font-weight:400"><input type="checkbox" name="remember" value="1" style="margin-right:7px"> Remember me</label>
            <button class="btn" type="submit" style="width:100%;margin-top:18px">Sign in to Admin</button>
        </form>
        <div style="margin-top:20px;padding-top:18px;border-top:1px solid #e2e8f0;text-align:center">
            <a href="{{ route('login') }}">← Regular user login</a>
            <p class="muted" style="font-size:12px;margin:10px 0 0">Admin access is controlled by the server-side <code>ADMIN_EMAILS</code> allowlist.</p>
        </div>
    </div>
</div>
@endsection
