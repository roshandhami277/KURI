<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Kuri</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ route('dashboard') }}">Kuri</a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-button" type="submit">Log out</button>
        </form>
    </header>

    <main class="dashboard-page">
        <p class="eyebrow">Protected page</p>
        <h1>Hello, {{ auth()->user()->name }}.</h1>
        <p>You can see this page because you are logged in.</p>
    </main>
</body>
</html>
