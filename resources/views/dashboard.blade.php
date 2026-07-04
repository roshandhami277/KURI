@extends('layouts.nav')

@section('title', 'Painel')

@section('content')
    @php
        $user = auth()->user();
    @endphp

    <div class="page-cover page-cover-dashboard" aria-hidden="true"></div>

    <section class="dashboard-hero">
        <div>
            <p>Painel</p>
            <h1>Olá, {{ auth()->user()->name }}.</h1>
            <span>
                @if ($user->isAdmin())
                    Este é o teu espaço de administrador.
                @elseif ($user->isTeacher())
                    Este é o teu espaço de professor.
                @else
                    Este é o teu espaço privado no Kuri.
                @endif
            </span>
        </div>

        <div class="dashboard-profile-card">
            <strong>{{ strtoupper(substr($user->name, 0, 1)) }}</strong>
            <div>
                <span>{{ ucfirst($user->role) }}</span>
                <small>{{ $user->course?->name ?? 'Conta Kuri' }}</small>
            </div>
        </div>
    </section>

    <section class="dashboard-strip">
        <div>
            <span class="material-symbols-outlined">person</span>
            <small>Nome</small>
            <strong>{{ $user->name }}</strong>
        </div>

        <div>
            <span class="material-symbols-outlined">mail</span>
            <small>Email</small>
            <strong>{{ $user->email }}</strong>
        </div>

        <div>
            <span class="material-symbols-outlined">school</span>
            <small>Curso</small>
            <strong>{{ $user->course?->name ?? 'Não selecionado' }}</strong>
        </div>
    </section>

    <section class="dashboard-grid">
        @if ($user->isAdmin())
            <a class="dashboard-card" href="{{ route('admin.index') }}">
                <span class="material-symbols-outlined">admin_panel_settings</span>
                <h2>Painel de administração</h2>
                <p>Gere utilizadores, professores e definições escolares do Kuri.</p>
            </a>

            <a class="dashboard-card" href="{{ route('chat') }}">
                <span class="material-symbols-outlined">chat_bubble</span>
                <h2>Chat</h2>
                <p>Abre a comunicação escolar e os grupos de professores.</p>
            </a>

            <a class="dashboard-card" href="{{ route('news') }}">
                <span class="material-symbols-outlined">newsmode</span>
                <h2>Notícias</h2>
                <p>Vê e publica avisos da escola.</p>
            </a>
        @elseif ($user->isTeacher())
            <a class="dashboard-card" href="{{ route('notes') }}">
                <span class="material-symbols-outlined">book_4</span>
                <h2>Apontamentos</h2>
                <p>Prepara apontamentos que depois podem ser partilhados com grupos.</p>
            </a>

            <a class="dashboard-card" href="{{ route('chat') }}">
                <span class="material-symbols-outlined">chat_bubble</span>
                <h2>Chat</h2>
                <p>Cria grupos e fala com os alunos.</p>
            </a>

            <a class="dashboard-card" href="{{ route('news') }}">
                <span class="material-symbols-outlined">newsmode</span>
                <h2>Notícias</h2>
                <p>Publica atualizações escolares para os alunos.</p>
            </a>
        @else
            <a class="dashboard-card" href="{{ route('tasks') }}">
                <span>✓</span>
                <h2>Tarefas diárias</h2>
                <p>Planeia o que precisas de terminar hoje.</p>
            </a>

            <a class="dashboard-card" href="{{ route('calendar') }}">
                <span class="material-symbols-outlined">event_note</span>
                <h2>Calendário</h2>
                <p>Mantém trabalhos, testes e exames organizados.</p>
            </a>

            <a class="dashboard-card" href="{{ route('grades') }}">
                <span>↗</span>
                <h2>Notas</h2>
                <p>Insere as tuas notas por disciplina e vê o teu progresso.</p>
            </a>

            <a class="dashboard-card" href="{{ route('notes') }}">
                <span class="material-symbols-outlined">book_4</span>
                <h2>Apontamentos</h2>
                <p>Escreve apontamentos das disciplinas e guarda ideias úteis de estudo.</p>
            </a>

            <a class="dashboard-card" href="{{ route('chat') }}">
                <span class="material-symbols-outlined">chat_bubble</span>
                <h2>Chat</h2>
                <p>Fala com o teu grupo de curso e grupos de professores.</p>
            </a>

            <a class="dashboard-card" href="{{ route('news') }}">
                <span class="material-symbols-outlined">newsmode</span>
                <h2>Notícias</h2>
                <p>Lê avisos e atualizações da escola.</p>
            </a>
        @endif
    </section>
@endsection
