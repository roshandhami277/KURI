@extends('layouts.nav')

@section('title', 'Daily tasks')

@section('content')
    <div class="page-heading">
        <p>Daily planner</p>
        <h1>Daily tasks</h1>
        <span>Write a task, press Enter, and it becomes a checkbox.</span>
    </div>

    @if ($errors->any())
        <div class="error-box">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <section class="single-todo-card">
        <h2>Today's to-do list</h2>

        <div class="todo-lines" data-task-list>
            @foreach ($tasks as $task)
                <label class="todo-line" data-toggle-url="{{ route('tasks.toggle', $task) }}">
                    <input type="checkbox" @checked($task->completed_at)>
                    <span>{{ $task->title }}</span>
                </label>
            @endforeach
        </div>

        <label class="writing-line">
            <input type="text" placeholder="Write a task and press Enter" data-new-task>
        </label>
    </section>

    <script>
        const taskList = document.querySelector('[data-task-list]');
        const newTaskInput = document.querySelector('[data-new-task]');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function createTaskLine(task) {
            const line = document.createElement('label');
            line.className = 'todo-line';
            line.dataset.toggleUrl = task.toggle_url;
            line.innerHTML = `
                <input type="checkbox">
                <span></span>
            `;

            line.querySelector('span').textContent = task.title;
            taskList.appendChild(line);
        }

        newTaskInput.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();

            const title = newTaskInput.value.trim();

            if (title === '') {
                return;
            }

            fetch(@json(route('tasks.store')), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    title: title,
                    task_date: @json($selectedDate->format('Y-m-d')),
                }),
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (task) {
                    createTaskLine(task);
                    newTaskInput.value = '';
                });
        });

        taskList.addEventListener('change', function (event) {
            const checkbox = event.target;

            if (checkbox.type !== 'checkbox') {
                return;
            }

            fetch(checkbox.closest('[data-toggle-url]').dataset.toggleUrl, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
        });
    </script>
@endsection
