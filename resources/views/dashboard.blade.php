@extends('layouts.nav')

@section('title', 'Dashboard')

@section('content')
    <div class="page-heading">
        <p>Dashboard</p>
        <h1>Hello, {{ auth()->user()->name }}.</h1>
        <span>This is your private Kuri workspace.</span>
    </div>

    <section class="dashboard-grid">
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
    </section>
@endsection
