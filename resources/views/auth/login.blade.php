@extends('layouts.auth')

@section('title', 'Entrar')

@section('header-link')
    <p>Precisas de uma conta? <a href="{{ route('register') }}">Registar</a></p>
@endsection

@section('content')
    <section class="auth-card">
        <p class="eyebrow">Bem-vindo de volta</p>
        <h1>Entrar</h1>
        <p class="introduction">Insere os teus dados para abrir a tua conta privada do Kuri.</p>

        <form method="POST" action="{{ route('login.store') }}">
            {{-- CSRF protects this form from requests sent by another website. --}}
            @csrf

            <label for="email">Email da escola</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="password">Palavra-passe</label>
            <input id="password" name="password" type="password" required>
            @error('password')
                <p class="error">{{ $message }}</p>
            @enderror

            <label class="remember">
                <input name="remember" type="checkbox" value="1">
                Manter sessão iniciada
            </label>

            <button type="submit">Entrar</button>
        </form>
    </section>
@endsection
