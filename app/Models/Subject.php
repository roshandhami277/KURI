<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'is_active'])]
class Subject extends Model
{
    public function courses(): BelongsToMany
    {
        // This is the many-to-many connection:
        // one subject can belong to many courses.
        return $this->belongsToMany(Course::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }
}
