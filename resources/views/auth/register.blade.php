@extends('layouts.auth')

@section('title', 'Register')

@section('header-link')
    <p>Already registered? <a href="{{ route('login') }}">Log in</a></p>
@endsection

@section('content')
    <section class="auth-card">
        <p class="eyebrow">New student account</p>
        <h1>Register</h1>
        <p class="introduction">Use your AEL school email to create your Kuri account.</p>

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
            @error('name')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="email">School email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="name@ael.edu.pt" required>
            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>
            @error('password')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="password_confirmation">Repeat password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>

            <button type="submit">Create account</button>
        </form>
    </section>
@endsection
