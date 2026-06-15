<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | Kuri</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    {{-- This layout is shared by both authentication pages. --}}
    <header class="site-header">
        <a class="brand" href="{{ route('home') }}">Kuri</a>
        @yield('header-link')
    </header>

    <main class="auth-page">
        @yield('content')
    </main>
</body>
</html>
