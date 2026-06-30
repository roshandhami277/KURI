<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        // The selected date is the day the user clicked.
        // If the URL does not have a date, we use today's date.
        $selectedDate = Carbon::parse($request->query('date', today()->toDateString()));

        // The month is the month currently shown in the big calendar.
        // Example: if month=2026-06, this becomes 2026-06-01.
        $month = Carbon::parse($request->query('month', $selectedDate->format('Y-m')).'-01');

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
            'type' => ['required', 'string', 'max:40'],
            'event_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'notes' => ['nullable', 'string', 'max:800'],
            'reminder_enabled' => ['nullable', 'boolean'],
            'reminder_time' => ['nullable', 'required_if:reminder_enabled,1', 'date_format:H:i'],
        ]);

        // A checkbox sends nothing when it is off, so boolean() safely turns that into false.
        $validated['reminder_enabled'] = $request->boolean('reminder_enabled');

        // If reminder is off, do not store a reminder time.
        if (! $validated['reminder_enabled']) {
            $validated['reminder_time'] = null;
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
}
