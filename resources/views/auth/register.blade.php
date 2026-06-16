@extends('layouts.auth')

@section('title', 'Register')

@section('header-link')
    <p>Already registered? <a href="{{ route('login') }}">Log in</a></p>
@endsection

@section('content')
    <section class="auth-card">
        <p class="eyebrow">New student account</p>
        <h1>Register</h1>
        <p class="introduction">Use your AEL school email to create your Kuri account.</p>
        <p class="warning-text">Choose your course and class carefully. You cannot change them yourself later.</p>

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
            @error('name')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="email">School email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="a12345@alunos.ael.edu.pt" required>
            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="course_id">Course</label>
            <select id="course_id" name="course_id" required>
                <option value="">Choose your course</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>
                        {{ $course->name }}
                    </option>
                @endforeach
            </select>
            @error('course_id')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="school_class_id">Class</label>
            <select id="school_class_id" name="school_class_id" required>
                <option value="">Choose your class</option>
                @foreach ($schoolClasses as $schoolClass)
                    <option value="{{ $schoolClass->id }}" @selected(old('school_class_id') == $schoolClass->id)>
                        {{ $schoolClass->name }}
                    </option>
                @endforeach
            </select>
            @error('school_class_id')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>
            @error('password')
                <p class="error">{{ $message }}</p>
            @enderror

            <label for="password_confirmation">Repeat password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>

            <button type="submit">Create account</button>
        </form>
    </section>
@endsection
