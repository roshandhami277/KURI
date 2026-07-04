@extends('layouts.nav')

@section('title', 'Definições')

@section('content')
    <div class="page-heading">
        <p>A tua conta</p>
        <h1>Definições</h1>
        <span>Podes alterar o teu nome e palavra-passe aqui. A informação escolar importante fica bloqueada.</span>
    </div>

    @if (session('success'))
        <p class="success-message">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <div class="error-box">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <section class="form-card">
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf
            @method('PUT')

            <label for="name">Nome</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>

            <label for="email">Email</label>
            <input id="email" type="email" value="{{ $user->email }}" disabled>

            <label for="role">Tipo de conta</label>
            <input id="role" type="text" value="{{ ucfirst($user->role) }}" disabled>

            @if ($user->isStudent() || $user->isTeacher())
                {{-- Students and teachers belong to a course group, so we show it here but keep it locked. --}}
                <label for="course">Curso</label>
                <input id="course" type="text" value="{{ $user->course?->name ?? 'Não selecionado' }}" disabled>

                <label for="school_class">Turma</label>
                <input id="school_class" type="text" value="{{ $user->schoolClass?->name ?? 'Não selecionado' }}" disabled>
            @else
                {{-- Admin accounts control the app, so they do not need a course or class. --}}
                <p class="small-link">
                    As contas de administrador não pertencem a um curso ou turma.
                </p>
            @endif

            <label for="password">Nova palavra-passe</label>
            <input id="password" name="password" type="password" placeholder="Deixa vazio para manter a palavra-passe atual">

            <label for="password_confirmation">Confirmar palavra-passe</label>
            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Repete a nova palavra-passe">

            <button type="submit">Guardar definições</button>
        </form>

        @if ($user->isStudent() || $user->isTeacher())
            <p class="small-link">
                Precisas de mudar o email, curso, turma ou tipo de conta?
                <a href="mailto:example@ael.edu.pt?subject=Kuri account change">Enviar email</a>.
            </p>
        @else
            <p class="small-link">
                Precisas de mudar o email ou o tipo de conta?
                <a href="mailto:example@ael.edu.pt?subject=Kuri admin account change">Enviar email</a>.
            </p>
        @endif
    </section>
@endsection
