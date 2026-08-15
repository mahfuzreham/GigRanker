@extends('layouts.app')

@section('content')
<div style="max-width:480px;margin:auto">
    <h1>Create your account</h1>
    <p class="muted">Start building your freelance gig marketing projects.</p>
    <form class="card" method="POST" action="{{ route('register.store') }}">
        @csrf
        <label for="name">Name</label>
        <input id="name" name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required minlength="12" autocomplete="new-password">
        <p class="muted">Use at least 12 characters.</p>
        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required minlength="12" autocomplete="new-password">
        <button class="btn" type="submit" style="margin-top:20px">Create account</button>
    </form>
    <p class="muted">Already registered? <a href="{{ route('login') }}">Sign in</a>.</p>
</div>
@endsection
