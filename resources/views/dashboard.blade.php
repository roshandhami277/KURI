@extends('layouts.nav')

@section('title', 'Dashboard')

@section('content')
    @php
        $user = auth()->user();
    @endphp

    <div class="page-heading">
        <p>Dashboard</p>
        <h1>Hello, {{ auth()->user()->name }}.</h1>
        <span>
            @if ($user->isAdmin())
                This is your admin workspace.
            @elseif ($user->isTeacher())
                This is your teacher workspace.
            @else
                This is your private Kuri workspace.
            @endif
        </span>
    </div>

    <section class="dashboard-grid">
        @if ($user->isAdmin())
            <a class="dashboard-card" href="{{ route('admin.index') }}">
                <h2>Admin panel</h2>
                <p>Manage Kuri users, teachers, and school settings.</p>
            </a>

            <a class="dashboard-card" href="{{ route('chat') }}">
                <h2>Chat</h2>
                <p>Open school communication and teacher groups.</p>
            </a>

            <a class="dashboard-card" href="{{ route('news') }}">
                <h2>News</h2>
                <p>View and post school announcements.</p>
            </a>
        @elseif ($user->isTeacher())
            <a class="dashboard-card" href="{{ route('notes') }}">
                <h2>Notes</h2>
                <p>Prepare notes that can later be shared with groups.</p>
            </a>

            <a class="dashboard-card" href="{{ route('chat') }}">
                <h2>Chat</h2>
                <p>Create groups and talk with students.</p>
            </a>

            <a class="dashboard-card" href="{{ route('news') }}">
                <h2>News</h2>
                <p>Post school updates for students.</p>
            </a>
        @else
            <a class="dashboard-card" href="{{ route('tasks') }}">
                <h2>Daily tasks</h2>
                <p>Plan what you need to finish today.</p>
            </a>

            <a class="dashboard-card" href="{{ route('calendar') }}">
                <h2>Calendar</h2>
                <p>Keep homework, tests, and exams organised.</p>
            </a>

            <a class="dashboard-card" href="{{ route('grades') }}">
                <h2>Grades</h2>
                <p>Insert your subjects grades and see your progress.</p>
            </a>
        @endif
    </section>
@endsection
