@extends('layouts.nav')

@section('title', 'Daily tasks')

@section('content')
    <div class="page-heading">
        <p>Daily planner</p>
        <h1>Daily tasks</h1>
        <span>Add what you need to do today, mark it done, or look at another date.</span>
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

    <section class="tasks-layout">
        <div class="form-card">
            <form class="date-form" method="GET" action="{{ route('tasks') }}">
                <label for="date">View date</label>
                <input id="date" name="date" type="date" value="{{ $selectedDate->format('Y-m-d') }}" onchange="this.form.submit()">
            </form>

            <form method="POST" action="{{ route('tasks.store') }}">
                @csrf
                <input name="task_date" type="hidden" value="{{ $selectedDate->format('Y-m-d') }}">

                <label for="title">New task</label>
                <input id="title" name="title" type="text" placeholder="Example: finish maths homework" required>

                <button type="submit">Add task</button>
            </form>
        </div>

        <div class="task-list-card">
            <h2>{{ $selectedDate->isToday() ? 'Today' : $selectedDate->format('j F Y') }}</h2>

            @forelse ($tasks as $task)
                <article @class(['task-row', 'completed' => $task->completed_at])>
                    <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                        @csrf
                        @method('PATCH')
                        <button class="task-check" type="submit">{{ $task->completed_at ? '✓' : '' }}</button>
                    </form>

                    <div>
                        <strong>{{ $task->title }}</strong>
                        <small>{{ $task->completed_at ? 'Completed' : 'Not completed' }}</small>
                    </div>

                    <details>
                        <summary>Edit</summary>
                        <form method="POST" action="{{ route('tasks.update', $task) }}">
                            @csrf
                            @method('PUT')
                            <input name="title" type="text" value="{{ $task->title }}" required>
                            <input name="task_date" type="date" value="{{ $task->task_date->format('Y-m-d') }}" required>
                            <button type="submit">Save</button>
                        </form>

                        <form method="POST" action="{{ route('tasks.destroy', $task) }}">
                            @csrf
                            @method('DELETE')
                            <button class="danger-button" type="submit">Delete</button>
                        </form>
                    </details>
                </article>
            @empty
                <p class="empty-message">No tasks for this day yet.</p>
            @endforelse
        </div>
    </section>
@endsection
