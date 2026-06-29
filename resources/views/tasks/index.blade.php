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

        {{-- $tasks comes from DailyTaskController@index. Each task is one row from daily_tasks. --}}
        <div class="todo-lines" data-task-list>
            @foreach ($tasks as $task)
                <label
                    class="todo-line"
                    {{-- These URLs are used by JavaScript when the user edits, ticks, or deletes a task. --}}
                    data-update-url="{{ route('tasks.update', $task) }}"
                    data-toggle-url="{{ route('tasks.toggle', $task) }}"
                    data-delete-url="{{ route('tasks.destroy', $task) }}"
                >
                    <input type="checkbox" @checked($task->completed_at)>
                    <input type="text" value="{{ $task->title }}">
                </label>
            @endforeach
        </div>

        <label class="writing-line">
            <input type="text" placeholder="Write a task and press Enter" data-new-task>
        </label>
    </section>

    <script>
        // This is the visible list where saved task lines appear.
        const taskList = document.querySelector('[data-task-list]');

        // This is the empty input at the bottom where the user writes a new task.
        const newTaskInput = document.querySelector('[data-new-task]');

        // Laravel needs this token for POST/PATCH/DELETE requests.
        // It proves the request came from our own page.
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function createTaskLine(task) {
            // JavaScript creates the same HTML structure that Blade uses for saved tasks.
            const line = document.createElement('label');
            line.className = 'todo-line';

            // Save the URLs Laravel returned, so this new line can be edited later.
            line.dataset.updateUrl = task.update_url;
            line.dataset.toggleUrl = task.toggle_url;
            line.dataset.deleteUrl = task.delete_url;
            line.innerHTML = `
                <input type="checkbox">
                <input type="text">
            `;

            // Put the task title inside the text input.
            line.querySelector('input[type="text"]').value = task.title;

            // Add the new line to the visible list without reloading the whole page.
            taskList.appendChild(line);
        }

        function deleteTaskLine(line) {
            // Send DELETE to DailyTaskController@destroy.
            fetch(line.dataset.deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
            }).then(function () {
                // Remove the task from the page after Laravel deletes it from the database.
                line.remove();
                newTaskInput.focus();
            });
        }

        function updateTaskLine(line) {
            // Read the current text from this task line.
            const textInput = line.querySelector('input[type="text"]');
            const title = textInput.value.trim();

            // Do not save an empty task title.
            if (title === '') {
                return;
            }

            // Send PATCH to DailyTaskController@update.
            fetch(line.dataset.updateUrl, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    title: title,
                }),
            });
        }

        newTaskInput.addEventListener('keydown', function (event) {
            // Only create a new task when the user presses Enter.
            if (event.key !== 'Enter') {
                return;
            }

            // Stop Enter from submitting/reloading the page.
            event.preventDefault();

            const title = newTaskInput.value.trim();

            // If the input is empty, do nothing.
            if (title === '') {
                return;
            }

            // Send POST to DailyTaskController@store.
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
                    // The controller returns JSON with the new task and its URLs.
                    return response.json();
                })
                .then(function (task) {
                    // Show the new saved task on the page.
                    createTaskLine(task);
                    newTaskInput.value = '';
                });
        });

        taskList.addEventListener('change', function (event) {
            // This listens for checkbox changes inside the task list.
            const checkbox = event.target;

            if (checkbox.type !== 'checkbox') {
                return;
            }

            // Send PATCH to DailyTaskController@toggle.
            fetch(checkbox.closest('[data-toggle-url]').dataset.toggleUrl, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
        });

        taskList.addEventListener('keydown', function (event) {
            // If a saved task is empty and the user presses Backspace, delete it.
            const textInput = event.target;

            if (textInput.type !== 'text' || event.key !== 'Backspace' || textInput.value !== '') {
                return;
            }

            event.preventDefault();
            deleteTaskLine(textInput.closest('[data-delete-url]'));
        });

        taskList.addEventListener('focusout', function (event) {
            // focusout runs when the user leaves a saved task input.
            // That is when we save edited task text.
            const textInput = event.target;

            if (textInput.type !== 'text') {
                return;
            }

            const line = textInput.closest('[data-update-url]');

            // If the user erased all text, delete the task row.
            if (textInput.value.trim() === '') {
                deleteTaskLine(line);
                return;
            }

            // Otherwise save the new title.
            updateTaskLine(line);
        });
    </script>
@endsection
