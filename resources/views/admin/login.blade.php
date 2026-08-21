@extends('layouts.app')
@section('content')
<div style="max-width:520px;margin:40px auto">
    <div class="card">
        <span class="badge">GigRanker Admin</span>
        <h1 style="margin:12px 0 6px">Admin Sign In</h1>
        <p class="muted">Authorized administrators only.</p>
        @if($errors->any())<div class="alert error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
        <form method="POST" action="{{ route('admin.login.store') }}">
            @csrf
            <label for="email">Admin email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
            <label style="font-weight:400"><input type="checkbox" name="remember" value="1" style="width:auto;margin-right:7px"> Remember me</label>
            <button class="btn" type="submit" style="width:100%;margin-top:18px">Sign in to Admin</button>
        </form>
        <p class="muted" style="margin-top:18px;font-size:13px">Admin access is controlled by the server-side <code>ADMIN_EMAILS</code> allowlist. Your admin password is never stored in GitHub.</p>
    </div>
</div>
@endsection
