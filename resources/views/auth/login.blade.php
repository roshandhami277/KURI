@extends('layouts.auth')

@section('title', 'Log in')

@section('header-link')
    <p>Need an account? <a href="{{ route('register') }}">Register</a></p>
@endsection

@section('content')
    <section class="auth-card">
        <p class="eyebrow">Welcome back</p>
        <h1>Log in</h1>
        <p class="introduction">Enter your details to open your private Kuri account.</p>

        <form method="POST" action="{{ route('login.store') }}">
            {{-- CSRF protects this form from requests sent by another website. --}}
            @csrf

            <label for="email">School email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>
            @error('password')
                <p class="error">{{ $message }}</p>
            @enderror

            <label class="remember">
                <input name="remember" type="checkbox" value="1">
                Keep me logged in
            </label>

            <button type="submit">Log in</button>
        </form>
    </section>
@endsection
