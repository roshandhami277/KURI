<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | Kuri</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chelsea+Market&display=swap" rel="stylesheet">
</head>
<body>
    {{-- This layout is shared by both authentication pages. --}}
    <header class="site-header">
        <a class="brand" href="{{ route('home') }}">Kuri</a>
        <div class="header-right">
            @yield('header-link')
        </div>
    </header>

    <main class="auth-page">
        @yield('content')
    </main>
</body>
</html>
