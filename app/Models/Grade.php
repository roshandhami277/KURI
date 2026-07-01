<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'subject_id', 'title', 'grade', 'grade_date', 'notes'])]
class Grade extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    protected function casts(): array
    {
        return [
            'grade' => 'decimal:2',
            'grade_date' => 'date',
        ];
    }
}
