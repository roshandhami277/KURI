<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'is_active'])]
class Course extends Model
{
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subjects(): BelongsToMany
    {
        // This is the many-to-many connection:
        // one course can have many subjects.
        return $this->belongsToMany(Subject::class);
    }
}
