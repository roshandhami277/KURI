<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | Kuri</title>
    <script>
        if (localStorage.getItem('kuriSidebarCollapsed') === 'yes') {
            document.documentElement.classList.add('sidebar-is-collapsed');
        }

        if (localStorage.getItem('kuriDarkMode') === 'yes') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chelsea+Market&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,400,0,0" />
</head>
<body>
    @php
        $user = auth()->user();
    @endphp

    <div class="navigation-layout">
        <button class="mobile-menu-button" type="button" aria-label="Abrir navegação" data-mobile-menu-toggle>
            <span class="material-symbols-outlined">menu</span>
            <strong>Kuri</strong>
        </button>

        <button class="mobile-menu-backdrop" type="button" aria-label="Fechar navegação" data-mobile-menu-backdrop></button>

        {{-- This sidebar is shared by every Kuri page --}}
        <aside class="sidebar">
            <div class="sidebar-top">
                <a class="sidebar-brand" href="{{ $user->isAdmin() ? route('admin.index') : route('dashboard') }}">
                    <span>K</span>
                    Kuri
                </a>

                <button class="sidebar-toggle" type="button" aria-label="Esconder navegação" data-sidebar-toggle>
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
            </div>

            <div class="user-box">
                <strong>{{ auth()->user()->name }}</strong>
                <small>{{ auth()->user()->email }}</small>
            </div>

            <nav class="sidebar-links" aria-label="Kuri navigation">
                <p>Área de trabalho</p>

                @if ($user->isAdmin())
                    <a href="{{ route('admin.index') }}" @class(['active' => request()->routeIs('admin.index')])>
                        <span class="material-symbols-outlined">admin_panel_settings</span>Painel de administração
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>
                        <span>⌂</span> Painel
                    </a>
                @endif

                @if ($user->isStudent())
                    <a href="{{ route('tasks') }}" @class(['active' => request()->routeIs('tasks')])>
                        <span>✓</span> Tarefas diárias
                    </a>
                    <a href="{{ route('calendar') }}" @class(['active' => request()->routeIs('calendar')])>
                        <span class="material-symbols-outlined">event_note</span>Calendário
                    </a>
                    <a href="{{ route('grades') }}" @class(['active' => request()->routeIs('grades')])>
                        <span>↗</span> Notas
                    </a>
                @endif

                @if ($user->isStudent() || $user->isTeacher())
                    <a href="{{ route('notes') }}" @class(['active' => request()->routeIs('notes')])>
                        <span class="material-symbols-outlined">book_4</span> Apontamentos
                    </a>
                @endif

                <a href="{{ route('chat') }}" @class(['active' => request()->routeIs('chat')])>
                <span class="material-symbols-outlined">chat_bubble</span>Chat
                </a>
                <a href="{{ route('news') }}" @class(['active' => request()->routeIs('news')])>
                    <span class="material-symbols-outlined">newsmode</span> Notícias da escola
                </a>
            </nav>

            <div class="sidebar-bottom">
                <button class="theme-toggle-button" type="button" data-theme-toggle>
                    <span class="material-symbols-outlined">dark_mode</span>
                    <strong>Modo escuro</strong>
                </button>

                <a href="{{ route('settings') }}" @class(['active' => request()->routeIs('settings')])>
                    <span class="material-symbols-outlined">settings</span>Definições
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"><span>↪</span> Sair</button>
                </form>
            </div>
        </aside>

        <main class="page-content">
            @yield('content')
        </main>
    </div>

    <script>
        const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
        const sidebarToggleIcon = sidebarToggle.querySelector('span');
        const mobileMenuToggle = document.querySelector('[data-mobile-menu-toggle]');
        const mobileMenuBackdrop = document.querySelector('[data-mobile-menu-backdrop]');
        const sidebarLinks = document.querySelectorAll('.sidebar a');
        const themeToggle = document.querySelector('[data-theme-toggle]');
        const themeToggleIcon = themeToggle.querySelector('span');
        const themeToggleText = themeToggle.querySelector('strong');
        const darkModeText = @json('Modo escuro');
        const lightModeText = @json('Modo claro');
        const showNavigationText = @json('Mostrar navegação');
        const hideNavigationText = @json('Esconder navegação');

        function updateSidebarToggleIcon() {
            const collapsed = document.documentElement.classList.contains('sidebar-is-collapsed');

            sidebarToggleIcon.textContent = collapsed ? 'chevron_right' : 'chevron_left';
            sidebarToggle.setAttribute('aria-label', collapsed ? showNavigationText : hideNavigationText);
        }

        updateSidebarToggleIcon();

        sidebarToggle.addEventListener('click', function () {
            document.documentElement.classList.toggle('sidebar-is-collapsed');

            const collapsed = document.documentElement.classList.contains('sidebar-is-collapsed');
            localStorage.setItem('kuriSidebarCollapsed', collapsed ? 'yes' : 'no');
            updateSidebarToggleIcon();
        });

        function closeMobileMenu() {
            document.documentElement.classList.remove('mobile-menu-is-open');
        }

        mobileMenuToggle.addEventListener('click', function () {
            document.documentElement.classList.toggle('mobile-menu-is-open');
        });

        mobileMenuBackdrop.addEventListener('click', closeMobileMenu);

        sidebarLinks.forEach(function (link) {
            link.addEventListener('click', closeMobileMenu);
        });

        function updateThemeButton() {
            const darkMode = document.documentElement.classList.contains('dark-mode');

            themeToggleIcon.textContent = darkMode ? 'light_mode' : 'dark_mode';
            themeToggleText.textContent = darkMode ? lightModeText : darkModeText;
        }

        updateThemeButton();

        themeToggle.addEventListener('click', function () {
            document.documentElement.classList.toggle('dark-mode');

            const darkMode = document.documentElement.classList.contains('dark-mode');
            localStorage.setItem('kuriDarkMode', darkMode ? 'yes' : 'no');
            updateThemeButton();
        });
    </script>
</body>
</html>
