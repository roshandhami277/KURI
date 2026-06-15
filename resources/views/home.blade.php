<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kuri</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    {{-- Top navigation --}}
    <header class="site-header">
        <a class="brand" href="#home" aria-label="Kuri home">
            <span class="brand-mark">K</span>
            <span>Kuri</span>
        </a>

        <div class="header-actions">
            @auth
                <a href="{{ route('dashboard') }}">Dashboard</a>
            @else
                <a href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}">Register</a>
            @endauth
        </div>
    </header>

    <main class="dashboard-page">
        <p class="eyebrow">A school workspace</p>
        <h1>Kuri</h1>
        <p class="introduction">Organise your school work in one simple place.</p>
    </main>
</body>
</html>
