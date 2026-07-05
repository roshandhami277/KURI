<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        // The selected date is the day the user clicked.
        // If the URL does not have a date, we use today's date.
        $selectedDate = $this->dateFromQuery($request->query('date'), today());

        // The month is the month currently shown in the big calendar.
        // Example: if month=2026-06, this becomes 2026-06-01.
        $month = $this->monthFromQuery($request->query('month'), $selectedDate);

        // Get all events for the logged-in user for this visible month.
        // This keeps the calendar private because it starts from $request->user().
        $events = $request->user()
            ->calendarEvents()
            ->whereBetween('event_date', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ])
            ->orderBy('start_time')
            ->get();

        // Group events by date so the Blade page can easily show events inside each day box.
        $eventsByDate = $events->groupBy(fn (CalendarEvent $event) => $event->event_date->toDateString());
        $timelineStartHour = 7;
        $timelineEndHour = 22;
        $now = now();
        // This is the time that goes into the add-event form.
        // If the URL has start=14:00, use that. If not, use the current time.
        $formStartTime = $request->query('start', $now->format('H:i'));
        if (! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $formStartTime)) {
            $formStartTime = $now->format('H:i');
        }
        $minutesFromStart = (($now->hour * 60) + $now->minute) - ($timelineStartHour * 60);
        [$formHour, $formMinute] = array_map('intval', explode(':', $formStartTime));
        $formMinutesFromStart = (($formHour * 60) + $formMinute) - ($timelineStartHour * 60);
        $totalTimelineMinutes = (($timelineEndHour + 1) - $timelineStartHour) * 60;
        $currentTimePosition = max(0, min(100, ($minutesFromStart / $totalTimelineMinutes) * 100));
        $formTimePosition = max(0, min(100, ($formMinutesFromStart / $totalTimelineMinutes) * 100));

        return view('calendar.index', [
            'selectedDate' => $selectedDate,
            'month' => $month,
            'previousMonth' => $month->copy()->subMonth(),
            'nextMonth' => $month->copy()->addMonth(),
            // blankDays tells Blade how many empty squares to print before day 1.
            'blankDays' => $month->copy()->startOfMonth()->dayOfWeekIso - 1,
            'daysInMonth' => $month->daysInMonth,
            'eventsByDate' => $eventsByDate,
            // These are the events shown in the right-side timeline for the clicked day.
            'selectedEvents' => $eventsByDate->get($selectedDate->toDateString(), collect()),
            'timelineStartHour' => $timelineStartHour,
            'timelineEndHour' => $timelineEndHour,
            'currentTimePosition' => $currentTimePosition,
            'formStartTime' => $formStartTime,
            'formTimePosition' => $formTimePosition,
            'formIsOpen' => $request->boolean('open'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Validate the form before saving it in the database.
        $validated = $this->validateEvent($request);

        // Create one row in calendar_events.
        // Laravel automatically fills user_id because we start from the logged-in user.
        $request->user()->calendarEvents()->create($validated);

        return $this->backToCalendar($validated['event_date']);
    }

    public function update(Request $request, CalendarEvent $event): RedirectResponse
    {
        // Do not let one student edit another student's calendar event.
        abort_unless($event->user_id === $request->user()->id, 403);

        $validated = $this->validateEvent($request);

        // Save the changed event values in calendar_events.
        $event->update($validated);

        return $this->backToCalendar($validated['event_date']);
    }

    public function destroy(Request $request, CalendarEvent $event): RedirectResponse
    {
        // Do not let one student delete another student's calendar event.
        abort_unless($event->user_id === $request->user()->id, 403);

        // Save the date before deleting so we can return to the same day.
        $eventDate = $event->event_date->toDateString();

        // Delete the row from calendar_events.
        $event->delete();

        return $this->backToCalendar($eventDate);
    }

    private function validateEvent(Request $request): array
    {
        // These names match the name="" attributes in the Blade form.
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(['Homework', 'Test', 'Exam', 'Presentation', 'Other'])],
            'event_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:800'],
        ], [
            'title.required' => 'Escreve um título para o evento.',
            'title.max' => 'O título do evento é demasiado grande.',
            'type.required' => 'Escolhe o tipo de evento.',
            'type.in' => 'Escolhe um tipo de evento válido.',
            'event_date.required' => 'Escolhe uma data para o evento.',
            'event_date.date' => 'Escolhe uma data válida.',
            'start_time.required' => 'Escolhe a hora de início.',
            'start_time.date_format' => 'Escolhe uma hora de início válida.',
            'end_time.date_format' => 'Escolhe uma hora de fim válida.',
            'notes.max' => 'Os apontamentos do evento são demasiado grandes.',
        ]);

        // A school event cannot finish before it starts.
        // It also should not finish at the exact same minute, because then it has no duration.
        if (! empty($validated['end_time']) && $validated['end_time'] <= $validated['start_time']) {
            throw ValidationException::withMessages([
                'end_time' => 'A hora de fim tem de ser depois da hora de início.',
            ]);
        }

        return $validated;
    }

    private function backToCalendar(string $date): RedirectResponse
    {
        // After creating/editing/deleting, return to the same date and month.
        return redirect()->route('calendar', [
            'date' => $date,
            'month' => Carbon::parse($date)->format('Y-m'),
        ]);
    }

    private function dateFromQuery(mixed $date, Carbon $fallback): Carbon
    {
        if (! is_string($date)) {
            return $fallback;
        }

        try {
            return $date !== '' ? Carbon::parse($date) : $fallback;
        } catch (\Throwable) {
            return $fallback;
        }
    }

    private function monthFromQuery(mixed $month, Carbon $fallback): Carbon
    {
        if (! is_string($month)) {
            return $fallback->copy()->startOfMonth();
        }

        try {
            return $month !== '' ? Carbon::parse($month.'-01') : $fallback->copy()->startOfMonth();
        } catch (\Throwable) {
            return $fallback->copy()->startOfMonth();
        }
    }
}
