@extends('layouts.nav')

@section('title', 'Settings')

@section('content')
    <div class="page-heading">
        <p>Your account</p>
        <h1>Settings</h1>
        <span>You can change your name here. School information is locked after registration.</span>
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

            <label for="course">Course</label>
            <input id="course" type="text" value="{{ $user->course->name }}" disabled>

            <label for="school_class">Class</label>
            <input id="school_class" type="text" value="{{ $user->schoolClass->name }}" disabled>

            <button type="submit">Save name</button>
        </form>

        <p class="small-link">
            Need to change email, course, or class?
            <a href="mailto:example@ael.edu.pt?subject=Kuri account change">Send an email</a>.
        </p>
    </section>
@endsection
