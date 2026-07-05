<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'title', 'type', 'event_date', 'start_time', 'end_time', 'notes'])]
class CalendarEvent extends Model
{
    public function user(): BelongsTo
    {
        // Each event belongs to one user.
        // Laravel connects calendar_events.user_id to users.id.
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            // This lets us use date functions like format() on event_date.
            'event_date' => 'date',
        ];
    }
}
