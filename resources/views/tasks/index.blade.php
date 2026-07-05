@extends('layouts.nav')

@section('title', 'Tarefas diárias')

@section('content')
    @php
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->whereNotNull('completed_at')->count();
        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
    @endphp

    <div class="page-cover page-cover-tasks" aria-hidden="true"></div>

    <div class="page-heading">
        <p>Planeador diário</p>
        <h1>Tarefas diárias</h1>
        <span>Escreve uma tarefa, carrega em Enter, e ela torna-se uma checkbox.</span>
    </div>

    @if ($errors->any())
        <div class="error-box">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <section class="daily-task-layout">
        <section class="single-todo-card">
            <div class="todo-card-top">
                <div>
                    <p>Lista</p>
                    <h2>Lista de tarefas de hoje</h2>
                </div>

                <span data-task-counter>{{ $completedTasks }}/{{ $totalTasks }}</span>
            </div>

            <p class="task-error-message" data-task-error hidden></p>

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
                <input type="text" placeholder="Escreve uma tarefa e carrega em Enter" data-new-task>
            </label>
        </section>

        <aside class="daily-side-card">
            <div class="daily-date-box">
                <p>Hoje</p>
                <strong>{{ $selectedDate->format('d M') }}</strong>
                <span>{{ $selectedDate->format('l') }}</span>
            </div>

            <div class="daily-progress-box" style="--progress: {{ $progress }}%;" data-progress-box>
                <div>
                    <span>Progresso</span>
                    <strong data-progress-number>{{ $progress }}%</strong>
                </div>

                <div class="daily-progress-bar">
                    <span></span>
                </div>
            </div>

            <div class="daily-tip-box">
                <p>Pequeno plano</p>
                <ul>
                    <li>Começa pela tarefa mais fácil.</li>
                    <li>Mantém tarefas escolares e pessoais juntas.</li>
                    <li>Elimina uma tarefa apagando o texto e carregando em Backspace.</li>
                </ul>
            </div>
        </aside>
    </section>

    <script>
        // This is the visible list where saved task lines appear.
        const taskList = document.querySelector('[data-task-list]');

        // This is the empty input at the bottom where the user writes a new task.
        const newTaskInput = document.querySelector('[data-new-task]');

        // These elements show the small progress numbers on the page.
        const taskCounter = document.querySelector('[data-task-counter]');
        const progressBox = document.querySelector('[data-progress-box]');
        const progressNumber = document.querySelector('[data-progress-number]');
        const taskError = document.querySelector('[data-task-error]');

        // Laravel needs this token for POST/PATCH/DELETE requests.
        // It proves the request came from our own page.
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function showTaskError(message) {
            taskError.textContent = message;
            taskError.hidden = false;
        }

        function clearTaskError() {
            taskError.textContent = '';
            taskError.hidden = true;
        }

        function readValidationMessage(response) {
            return response.json().then(function (data) {
                if (data.message) {
                    return data.message;
                }

                if (data.errors) {
                    const firstField = Object.keys(data.errors)[0];

                    if (firstField && data.errors[firstField][0]) {
                        return data.errors[firstField][0];
                    }
                }

                return 'Não foi possível guardar a tarefa.';
            });
        }

        function updateDailyProgress() {
            // Count all saved task lines currently visible on the page.
            const taskLines = taskList.querySelectorAll('.todo-line');
            const total = taskLines.length;

            // Count only the task lines with a checked checkbox.
            const completed = taskList.querySelectorAll('.todo-line input[type="checkbox"]:checked').length;

            // Avoid division by zero when there are no tasks.
            const progress = total > 0 ? Math.round((completed / total) * 100) : 0;

            // Update the small "0/0" text near the title.
            taskCounter.textContent = completed + '/' + total;

            // Update the percentage text and the black progress bar.
            progressNumber.textContent = progress + '%';
            progressBox.style.setProperty('--progress', progress + '%');
        }

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
            updateDailyProgress();
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
                updateDailyProgress();
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

            clearTaskError();

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
            }).then(function (response) {
                if (! response.ok) {
                    return readValidationMessage(response).then(showTaskError);
                }
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
                    if (! response.ok) {
                        return readValidationMessage(response).then(function (message) {
                            showTaskError(message);
                            return null;
                        });
                    }

                    // The controller returns JSON with the new task and its URLs.
                    return response.json();
                })
                .then(function (task) {
                    if (! task) {
                        return;
                    }

                    // Show the new saved task on the page.
                    clearTaskError();
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
            }).then(function () {
                updateDailyProgress();
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
