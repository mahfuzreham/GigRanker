@extends('layouts.app')

@section('content')
<div style="max-width:480px;margin:auto">
    <h1>Admin Sign in</h1>
    <p class="muted">Restricted GigRanker administration access.</p>

    @if ($errors->any())
        <div class="card" style="margin-bottom:16px">
            @foreach ($errors->all() as $error)
                <p style="margin:0 0 6px">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form class="card" method="POST" action="{{ route('admin.login.store') }}">
        @csrf
        <label for="email">Admin email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">

        <label style="font-weight:400">
            <input type="checkbox" name="remember" value="1" style="width:auto;margin-right:7px">
            Remember me
        </label>

        <button class="btn" type="submit" style="margin-top:20px">Admin Sign in</button>
    </form>

    <p class="muted"><a href="{{ route('login') }}">Regular user login</a></p>
</div>
@endsection
