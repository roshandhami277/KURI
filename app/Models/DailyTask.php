<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'title', 'task_date', 'completed_at'])]
class DailyTask extends Model
{
    public function user(): BelongsTo
    {
        // Each daily task belongs to one user.
        // This connects daily_tasks.user_id to users.id.
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            // Tell Laravel to treat task_date as a date object instead of plain text.
            'task_date' => 'date',
            // completed_at is either null or a date/time object.
            'completed_at' => 'datetime',
        ];
    }
}
