@extends('layouts.auth')

@section('title', 'Registar')

@section('header-link')
    <p>Já tens conta? <a href="{{ route('login') }}">Entrar</a></p>
@endsection

@section('content')
    <section class="auth-card">
        <p class="eyebrow">Nova conta</p>
        <h1>Registar</h1>
        <p class="introduction">Usa o teu email escolar AEL para criar a tua conta Kuri.</p>
        <p class="warning-text">Emails de alunos criam contas de aluno. Emails de professores criam contas de professor. Os professores devem escolher o seu curso e turma de DT.</p>

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            <label for="name">Nome</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
            @error('name')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="email">Email da escola</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="a12345@alunos.ael.edu.pt" required>
            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="course_id">Curso</label>
            <select id="course_id" name="course_id">
                <option value="">Escolhe o teu curso</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>
                        {{ $course->name }}
                    </option>
                @endforeach
            </select>
            @error('course_id')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="school_class_id">Turma / grupo</label>
            <select id="school_class_id" name="school_class_id">
                <option value="">Escolhe a tua turma</option>
                @foreach ($schoolClasses as $schoolClass)
                    <option value="{{ $schoolClass->id }}" @selected(old('school_class_id') == $schoolClass->id)>
                        {{ $schoolClass->name }}
                    </option>
                @endforeach
            </select>
            @error('school_class_id')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="password">Palavra-passe</label>
            <input id="password" name="password" type="password" required>
            @error('password')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="password_confirmation">Repetir palavra-passe</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>

            <button type="submit">Criar conta</button>
        </form>
    </section>
@endsection
