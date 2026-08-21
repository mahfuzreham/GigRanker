@extends('layouts.app')

@section('content')
<div style="max-width:480px;margin:20px auto">
    <div class="card" style="padding:32px">
        <div style="text-align:center;margin-bottom:24px">
            <span class="badge">Secure admin area</span>
            <h1 style="margin:12px 0 5px">Admin Sign In</h1>
            <p class="muted" style="margin:0">Restricted GigRanker administration access.</p>
        </div>
        @if ($errors->any())
            <div class="alert error">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        @endif
        <form method="POST" action="{{ route('admin.login.store') }}">
            @csrf
            <label for="email">Admin email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" autofocus>
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
            <label style="font-weight:400"><input type="checkbox" name="remember" value="1" style="margin-right:7px"> Remember me</label>
            <button class="btn" type="submit" style="width:100%;margin-top:18px">Admin Sign in</button>
        </form>
        <p class="muted" style="text-align:center;margin:18px 0 0"><a href="{{ route('login') }}">← Regular user login</a></p>
    </div>
</div>
@endsection
