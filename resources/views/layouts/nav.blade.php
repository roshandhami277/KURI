<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | Kuri</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chelsea+Market&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,400,0,0" />
</head>
<body>
    <div class="navigation-layout">
        {{-- This sidebar is shared by every protected Kuri page. --}}
        <aside class="sidebar">
            <a class="sidebar-brand" href="{{ route('dashboard') }}">
                <span>K</span>
                Kuri
            </a>

            <div class="user-box">
                <strong>{{ auth()->user()->name }}</strong>
                <small>{{ auth()->user()->email }}</small>
            </div>

            <nav class="sidebar-links" aria-label="Kuri navigation">
                <p>Workspace</p>

                <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>
                    <span>⌂</span> Dashboard
                </a>
                <a href="{{ route('tasks') }}" @class(['active' => request()->routeIs('tasks')])>
                    <span>✓</span> Daily tasks
                </a>
                <a href="{{ route('calendar') }}" @class(['active' => request()->routeIs('calendar')])>
                    <span class="material-symbols-outlined">event_note</span>Calendar
                </a>
                <a href="{{ route('grades') }}" @class(['active' => request()->routeIs('grades')])>
                    <span>↗</span> Grades
                </a>
                <a href="{{ route('notes') }}" @class(['active' => request()->routeIs('notes')])>
                    <span class="material-symbols-outlined">book_4</span> Notes
                </a>
                <a href="{{ route('chat') }}" @class(['active' => request()->routeIs('chat')])>
                <span class="material-symbols-outlined">chat_bubble</span>Chat
                </a>
                <a href="{{ route('news') }}" @class(['active' => request()->routeIs('news')])>
                    <span class="material-symbols-outlined">newsmode</span> School news
                </a>
            </nav>

            <div class="sidebar-bottom">
                <a href="{{ route('settings') }}" @class(['active' => request()->routeIs('settings')])>
                    <span>⚙</span> Settings
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"><span>↪</span> Log out</button>
                </form>
            </div>
        </aside>

        <main class="page-content">
            @yield('content')
        </main>
    </div>
</body>
</html>
