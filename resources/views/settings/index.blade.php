@extends('layouts.nav')

@section('title', 'Settings')

@section('content')
    <div class="page-heading">
        <p>Your account</p>
        <h1>Settings</h1>
        <span>You can change your name and password here. Important school information stays locked.</span>
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

            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>

            <label for="email">Email</label>
            <input id="email" type="email" value="{{ $user->email }}" disabled>

            <label for="role">Account type</label>
            <input id="role" type="text" value="{{ ucfirst($user->role) }}" disabled>

            @if ($user->isStudent() || $user->isTeacher())
                {{-- Students and teachers belong to a course group, so we show it here but keep it locked. --}}
                <label for="course">Course</label>
                <input id="course" type="text" value="{{ $user->course?->name ?? 'Not selected' }}" disabled>

                <label for="school_class">Class</label>
                <input id="school_class" type="text" value="{{ $user->schoolClass?->name ?? 'Not selected' }}" disabled>
            @else
                {{-- Admin accounts control the app, so they do not need a course or class. --}}
                <p class="small-link">
                    Admin accounts do not belong to one course or class.
                </p>
            @endif

            <label for="password">New password</label>
            <input id="password" name="password" type="password" placeholder="Leave empty to keep current password">

            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Repeat the new password">

            <button type="submit">Save settings</button>
        </form>

        @if ($user->isStudent() || $user->isTeacher())
            <p class="small-link">
                Need to change email, course, class, or account type?
                <a href="mailto:example@ael.edu.pt?subject=Kuri account change">Send an email</a>.
            </p>
        @else
            <p class="small-link">
                Need to change email or account type?
                <a href="mailto:example@ael.edu.pt?subject=Kuri admin account change">Send an email</a>.
            </p>
        @endif
    </section>
@endsection
