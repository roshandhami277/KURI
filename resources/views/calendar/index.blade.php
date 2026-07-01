@extends('layouts.nav')

@section('title', 'Calendar')

@section('content')
    <div class="calendar-heading">
        <div class="page-heading">
            <p>Planner</p>
            <h1>Calendar</h1>
            <span>Click a day to open its timeline. Then click a timeline hour to create an event.</span>
        </div>
    </div>

    @if ($errors->any())
        <div class="error-box">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <section class="calendar-layout">
        <div class="calendar-card">
            <div class="calendar-top">
                {{-- These links change the month in the URL. CalendarController reads the URL. --}}
                <a href="{{ route('calendar', ['month' => $previousMonth->format('Y-m'), 'date' => $previousMonth->toDateString()]) }}">‹</a>
                <h2>{{ $month->format('F Y') }}</h2>
                <a href="{{ route('calendar', ['month' => $nextMonth->format('Y-m'), 'date' => $nextMonth->toDateString()]) }}">›</a>
            </div>

            <div class="calendar-weekdays">
                <span>Mon</span>
                <span>Tue</span>
                <span>Wed</span>
                <span>Thu</span>
                <span>Fri</span>
                <span>Sat</span>
                <span>Sun</span>
            </div>

            <div class="calendar-grid">
                {{-- Empty squares make day 1 start on the correct weekday. --}}
                @for ($blank = 0; $blank < $blankDays; $blank++)
                    <span class="calendar-empty"></span>
                @endfor

                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        // Build the real date for this calendar square.
                        $date = $month->copy()->day($day);
                        $dateText = $date->toDateString();
                        $dayEvents = $eventsByDate->get($dateText, collect());
                    @endphp

                    <a
                        @class([
                            'calendar-day',
                            'selected' => $dateText === $selectedDate->toDateString(),
                            'today' => $dateText === today()->toDateString(),
                        ])
                        href="{{ route('calendar', ['month' => $month->format('Y-m'), 'date' => $dateText]) }}#day-timeline"
                    >
                        <span class="calendar-date-number">{{ $day }}</span>

                        <div class="calendar-event-rows">
                            {{-- Events are shown as small rows below the date number. --}}
                            @foreach ($dayEvents->take(3) as $event)
                                <span class="event-row event-row-{{ strtolower($event->type) }}">
                                    {{ substr($event->start_time, 0, 5) }} {{ $event->title }}
                                </span>
                            @endforeach

                            @if ($dayEvents->count() > 3)
                                <em>+{{ $dayEvents->count() - 3 }} more</em>
                            @endif
                        </div>
                    </a>
                @endfor
            </div>
        </div>

    </section>

    <div id="day-timeline" class="timeline-overlay">
        <aside class="timeline-card">
            <a class="overlay-close" href="#">×</a>

            <div class="timeline-top">
                <div>
                    <p>Selected day</p>
                    <h2>{{ $selectedDate->format('l, j F Y') }}</h2>
                </div>
            </div>

            @if ($selectedEvents->isEmpty())
                <p class="timeline-empty">No events for this day yet.</p>
            @endif

            <div class="day-timeline">
                @if ($selectedDate->isToday())
                    {{-- This line shows the current time on today's timeline. --}}
                    <div class="current-time-line" style="top: {{ $currentTimePosition }}%">
                        <span>{{ now()->format('H:i') }}</span>
                    </div>
                @endif

                @if ($formIsOpen)
                    {{-- This line shows the time that was chosen when the add form opened. --}}
                    <div class="selected-time-line" style="top: {{ $formTimePosition }}%">
                        <span>{{ $formStartTime }}</span>
                    </div>
                @endif

                @for ($hour = $timelineStartHour; $hour <= $timelineEndHour; $hour++)
                    @php
                        $hourText = str_pad($hour, 2, '0', STR_PAD_LEFT).':00';
                        // Put each event inside the row that matches its start hour.
                        $hourEvents = $selectedEvents->filter(function ($event) use ($hour) {
                            return (int) substr($event->start_time, 0, 2) === $hour;
                        });
                    @endphp

                    <div class="timeline-hour-row">
                        <time>{{ $hourText }}</time>

                        <div
                            class="timeline-hour-content"
                            data-open-event-url="{{ route('calendar', ['month' => $month->format('Y-m'), 'date' => $selectedDate->toDateString(), 'start' => $hourText, 'open' => 1]) }}#day-timeline"
                        >
                            @foreach ($hourEvents as $event)
                                <article class="timeline-event-card">
                                    <div>
                                        <strong>{{ $event->title }}</strong>
                                        <span>
                                            {{ substr($event->start_time, 0, 5) }}
                                            @if ($event->end_time)
                                                - {{ substr($event->end_time, 0, 5) }}
                                            @endif
                                            · {{ $event->type }}
                                        </span>

                                        @if ($event->notes)
                                            <p>{{ $event->notes }}</p>
                                        @endif

                                        @if ($event->reminder_enabled)
                                            <p>Reminder email at {{ substr($event->reminder_time, 0, 5) }}</p>
                                        @endif
                                    </div>

                                    <div class="timeline-actions">
                                        <a href="#edit-event-{{ $event->id }}" aria-label="Edit {{ $event->title }}">
                                            <span>✎</span>
                                        </a>

                                        {{-- This form sends DELETE to CalendarController@destroy. --}}
                                        <form method="POST" action="{{ route('calendar.destroy', $event) }}">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" aria-label="Delete {{ $event->title }}">
                                                <span>×</span>
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endfor
            </div>

            @if ($formIsOpen)
                <div class="timeline-form-layer">
                    {{-- This form sends POST to CalendarController@store. --}}
                    <form class="event-form timeline-event-form" method="POST" action="{{ route('calendar.store') }}">
                        @csrf

                        <a
                            class="overlay-close"
                            href="{{ route('calendar', ['month' => $month->format('Y-m'), 'date' => $selectedDate->toDateString(), 'start' => $formStartTime]) }}#day-timeline"
                        >×</a>

                        <p>New event</p>
                        <h2>Add to calendar</h2>

                        <label for="event-date">Date</label>
                        <input id="event-date" name="event_date" type="date" value="{{ $selectedDate->toDateString() }}" required>

                        <label for="event-title">Title</label>
                        <input id="event-title" name="title" type="text" placeholder="Example: Biology test" required>

                        <label for="event-type">Type</label>
                        <select id="event-type" name="type" required>
                            <option value="Homework">Homework</option>
                            <option value="Test">Test</option>
                            <option value="Exam">Exam</option>
                            <option value="Presentation">Presentation</option>
                            <option value="Other">Other</option>
                        </select>

                        <div class="time-fields">
                            <label for="event-start-time">
                                Start
                                <input id="event-start-time" name="start_time" type="time" value="{{ $formStartTime }}" required>
                            </label>

                            <label for="event-end-time">
                                End
                                <input id="event-end-time" name="end_time" type="time">
                            </label>
                        </div>

                        <label for="event-notes">Notes</label>
                        <textarea id="event-notes" name="notes" placeholder="Small details"></textarea>

                        <label class="reminder-toggle-row">
                            <input class="reminder-toggle" name="reminder_enabled" type="checkbox" value="1">
                            Send a reminder by email later
                        </label>

                        <div class="reminder-time-field">
                            <label for="event-reminder-time">Reminder time</label>
                            <input id="event-reminder-time" name="reminder_time" type="time">
                        </div>

                        <button type="submit">Add event</button>
                    </form>
                </div>
            @endif
        </aside>
    </div>

    @foreach ($selectedEvents as $event)
        <div id="edit-event-{{ $event->id }}" class="event-overlay">
            {{-- This form sends PATCH to CalendarController@update for this exact event. --}}
            <form class="event-form" method="POST" action="{{ route('calendar.update', $event) }}">
                @csrf
                @method('PATCH')

                <a class="overlay-close" href="#">×</a>

                <p>Edit event</p>
                <h2>{{ $event->title }}</h2>

                <label for="event-date-{{ $event->id }}">Date</label>
                <input id="event-date-{{ $event->id }}" name="event_date" type="date" value="{{ $event->event_date->toDateString() }}" required>

                <label for="event-title-{{ $event->id }}">Title</label>
                <input id="event-title-{{ $event->id }}" name="title" type="text" value="{{ $event->title }}" required>

                <label for="event-type-{{ $event->id }}">Type</label>
                <select id="event-type-{{ $event->id }}" name="type" required>
                    @foreach (['Homework', 'Test', 'Exam', 'Presentation', 'Other'] as $type)
                        <option value="{{ $type }}" @selected($event->type === $type)>{{ $type }}</option>
                    @endforeach
                </select>

                <div class="time-fields">
                    <label for="event-start-time-{{ $event->id }}">
                        Start
                        <input id="event-start-time-{{ $event->id }}" name="start_time" type="time" value="{{ substr($event->start_time, 0, 5) }}" required>
                    </label>

                    <label for="event-end-time-{{ $event->id }}">
                        End
                        <input id="event-end-time-{{ $event->id }}" name="end_time" type="time" value="{{ $event->end_time ? substr($event->end_time, 0, 5) : '' }}">
                    </label>
                </div>

                <label for="event-notes-{{ $event->id }}">Notes</label>
                <textarea id="event-notes-{{ $event->id }}" name="notes" placeholder="Small details">{{ $event->notes }}</textarea>

                <label class="reminder-toggle-row">
                    <input class="reminder-toggle" name="reminder_enabled" type="checkbox" value="1" @checked($event->reminder_enabled)>
                    Send a reminder by email later
                </label>

                <div class="reminder-time-field">
                    <label for="event-reminder-time-{{ $event->id }}">Reminder time</label>
                    <input id="event-reminder-time-{{ $event->id }}" name="reminder_time" type="time" value="{{ $event->reminder_time ? substr($event->reminder_time, 0, 5) : '' }}">
                </div>

                <button type="submit">Save event</button>
            </form>
        </div>
    @endforeach

    <script>
        // If the user clicks an empty place in the timeline, open the add-event form.
        // Clicking edit/delete buttons should still do their normal job, so we ignore those.
        document.querySelectorAll('[data-open-event-url]').forEach(function (hourLine) {
            hourLine.addEventListener('click', function (event) {
                if (event.target.closest('a, button, form, .timeline-event-card')) {
                    return;
                }

                window.location.href = hourLine.dataset.openEventUrl;
            });
        });
    </script>
@endsection
