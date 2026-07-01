@extends('layouts.nav')

@section('title', 'Grades')

@section('content')
    <div class="page-heading">
        <p>Progress</p>
        <h1>Grades</h1>
        <span>Select a subject, add your grades, and see your progress in the graph.</span>
    </div>

    @if ($errors->any())
        <div class="error-box">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if ($subjects->isEmpty())
        <section class="empty-card">
            <strong>No subjects found</strong>
            <p>Your course does not have subjects connected yet. Add them in <code>DatabaseSeeder.php</code> and run <code>php artisan db:seed</code>.</p>
        </section>
    @else
        <section class="grades-layout">
            <div class="grade-graph-card">
                <div class="grade-card-top">
                    <div>
                        <p>Graph</p>
                        <h2>Subject progress</h2>
                    </div>

                    <select data-graph-select aria-label="Choose graph subject">
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected($selectedSubjectId === $subject->id)>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grade-chart">
                    @foreach ($subjects as $subject)
                        @php
                            $graphPoints = $graphPointsBySubject[$subject->id] ?? [];
                        @endphp

                        <div class="grade-chart-panel" data-graph-panel="{{ $subject->id }}" @if ($selectedSubjectId !== $subject->id) hidden @endif>
                            @if (count($graphPoints) > 0)
                                <svg viewBox="0 0 820 330" role="img" aria-label="Grades graph for {{ $subject->name }}">
                                    <line x1="42" y1="14" x2="42" y2="298" />
                                    <line x1="42" y1="298" x2="796" y2="298" />

                                    @foreach ([20, 15, 10, 5, 0] as $mark)
                                        @php
                                            $y = 14 + (284 - (($mark / 20) * 284));
                                        @endphp
                                        <line class="chart-grid-line" x1="42" y1="{{ $y }}" x2="796" y2="{{ $y }}" />
                                        <text x="8" y="{{ $y + 6 }}">{{ $mark }}</text>
                                    @endforeach

                                    <polyline points="{{ collect($graphPoints)->map(fn ($point) => $point['x'].','.$point['y'])->join(' ') }}" />

                                    @foreach ($graphPoints as $point)
                                        <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" />
                                        <text class="chart-grade-label" x="{{ $point['x'] - 12 }}" y="{{ $point['y'] - 10 }}">{{ $point['label'] }}</text>
                                        <text class="chart-date-label" x="{{ $point['x'] - 20 }}" y="322">{{ $point['date'] }}</text>
                                    @endforeach
                                </svg>
                            @else
                                <p class="grade-empty-text">No grades for this subject yet.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <aside class="add-grade-card">
                <p>Add grade</p>
                <h2>New grade</h2>

                <form method="POST" action="{{ route('grades.store') }}">
                    @csrf

                    <label for="grade-subject">Subject</label>
                    <select id="grade-subject" name="subject_id" required>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected($selectedSubjectId === $subject->id)>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>

                    <label for="grade-title">Title</label>
                    <input id="grade-title" name="title" type="text" placeholder="Example: First test">

                    <div class="grade-small-row">
                        <div>
                            <label for="grade-value">Grade</label>
                            <input id="grade-value" name="grade" type="number" min="0" max="20" step="0.01" placeholder="0 - 20" required>
                        </div>

                        <div>
                            <label for="grade-date">Date</label>
                            <input id="grade-date" name="grade_date" type="date">
                        </div>
                    </div>

                    <label for="grade-notes">Notes</label>
                    <textarea id="grade-notes" name="notes" placeholder="Optional small note"></textarea>

                    <button type="submit">Add grade</button>
                </form>
            </aside>
        </section>

        <section class="grades-list-card">
            <div class="grade-card-top">
                <div>
                    <p>Recent grades</p>
                    <h2>Recent grades</h2>
                </div>

                <div class="grades-list-tools">
                    <select data-recent-select aria-label="Filter recent grades">
                        <option value="all" @selected($recentSubjectId === 'all')>All subjects</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected((string) $recentSubjectId === (string) $subject->id)>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>

                    <strong>Average: <span data-average-text>{{ $average ? number_format($average, 2) : '0.00' }}</span>/20</strong>

                </div>
            </div>

            <div class="grades-table-wrap">
                <table class="grades-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Title</th>
                            <th>Grade</th>
                            <th>Date</th>
                            <th>Notes</th>
                            <th class="grade-actions-column">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentGrades as $grade)
                            <tr data-grade-row data-subject-id="{{ $grade->subject_id }}" data-grade="{{ $grade->grade }}">
                                <td>{{ $grade->subject->name }}</td>
                                <td>{{ $grade->title ?: '-' }}</td>
                                <td>{{ number_format($grade->grade, 2) }}/20</td>
                                <td>{{ $grade->grade_date ? $grade->grade_date->format('d M Y') : '-' }}</td>
                                <td>{{ $grade->notes ?: '-' }}</td>
                                <td class="grade-actions-column">
                                    <details class="grade-row-menu">
                                        <summary aria-label="Open grade menu">
                                            <span class="material-symbols-outlined">more_horiz</span>
                                        </summary>

                                        <div>
                                            <a href="#edit-grade-{{ $grade->id }}">Edit</a>

                                            <form method="POST" action="{{ route('grades.destroy', $grade) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit">Delete</button>
                                            </form>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @endforeach

                        <tr data-empty-row @if ($recentGrades->isNotEmpty()) hidden @endif>
                            <td colspan="6">No grades for this subject yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        @foreach ($recentGrades as $grade)
            <div id="edit-grade-{{ $grade->id }}" class="event-overlay">
                <form class="event-form compact-grade-form" method="POST" action="{{ route('grades.update', $grade) }}">
                    @csrf
                    @method('PATCH')

                    <div class="event-form-top">
                        <div>
                            <p>Edit grade</p>
                            <h2>{{ $grade->subject->name }}</h2>
                        </div>

                        <a class="overlay-close" href="#">×</a>
                    </div>

                    <label for="edit-subject-{{ $grade->id }}">Subject</label>
                    <select id="edit-subject-{{ $grade->id }}" name="subject_id" required>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected($grade->subject_id === $subject->id)>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>

                    <label for="edit-title-{{ $grade->id }}">Title</label>
                    <input id="edit-title-{{ $grade->id }}" name="title" type="text" value="{{ $grade->title }}" placeholder="Example: First test">

                    <div class="grade-small-row">
                        <div>
                            <label for="edit-grade-amount-{{ $grade->id }}">Grade</label>
                            <input id="edit-grade-amount-{{ $grade->id }}" name="grade" type="number" min="0" max="20" step="0.01" value="{{ $grade->grade }}" required>
                        </div>

                        <div>
                            <label for="edit-date-{{ $grade->id }}">Date</label>
                            <input id="edit-date-{{ $grade->id }}" name="grade_date" type="date" value="{{ $grade->grade_date?->format('Y-m-d') }}">
                        </div>
                    </div>

                    <label for="edit-notes-{{ $grade->id }}">Notes</label>
                    <textarea id="edit-notes-{{ $grade->id }}" name="notes" placeholder="Optional small note">{{ $grade->notes }}</textarea>

                    <button type="submit">Save changes</button>
                </form>
            </div>
        @endforeach
    @endif

    <script>
        // These small actions only change what is already on the page, so they do not reload the browser.
        var graphSelect = document.querySelector('[data-graph-select]');
        var recentSelect = document.querySelector('[data-recent-select]');
        var averageText = document.querySelector('[data-average-text]');
        var emptyRow = document.querySelector('[data-empty-row]');

        if (graphSelect) {
            graphSelect.addEventListener('change', function () {
                document.querySelectorAll('[data-graph-panel]').forEach(function (panel) {
                    panel.hidden = panel.dataset.graphPanel !== graphSelect.value;
                });
            });
        }

        function filterRecentGrades() {
            if (! recentSelect) {
                return;
            }

                var total = 0;
                var visibleRows = 0;

                document.querySelectorAll('[data-grade-row]').forEach(function (row) {
                    var shouldShow = recentSelect.value === 'all' || row.dataset.subjectId === recentSelect.value;

                    row.hidden = ! shouldShow;

                    if (shouldShow) {
                        total = total + Number(row.dataset.grade);
                        visibleRows++;
                    }
                });

                if (averageText) {
                    averageText.textContent = visibleRows ? (total / visibleRows).toFixed(2) : '0.00';
                }

                if (emptyRow) {
                    emptyRow.hidden = visibleRows > 0;
                }
        }

        if (recentSelect) {
            recentSelect.addEventListener('change', filterRecentGrades);
            filterRecentGrades();
        }

    </script>
@endsection
