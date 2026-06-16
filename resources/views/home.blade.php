<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kuri</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chelsea+Market&display=swap" rel="stylesheet">
</head>
<body>
    {{-- Top navigation --}}
    <header class="site-header">
        <a class="brand" href="#home" aria-label="Kuri home">
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
        <p class="introduction">Organize everything in one simple place.</p>
    </main>
</body>
</html>
