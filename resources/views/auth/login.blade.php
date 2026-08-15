@extends('layouts.app')

@section('content')
<div style="max-width:480px;margin:auto">
    <h1>Sign in</h1>
    <p class="muted">Access your GigRanker projects.</p>
    <form class="card" method="POST" action="{{ route('login.store') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">
        <label style="font-weight:400"><input type="checkbox" name="remember" value="1" style="width:auto;margin-right:7px"> Remember me</label>
        <button class="btn" type="submit" style="margin-top:20px">Sign in</button>
    </form>
    <p class="muted">No account? <a href="{{ route('register') }}">Create one</a>.</p>
</div>
@endsection
