<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// These are the only user fields we allow forms to save for now.
#[Fillable(['name', 'email', 'password', 'role', 'course_id', 'school_class_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function dailyTasks(): HasMany
    {
        // One user can have many daily task rows.
        // Laravel knows these rows belong together because daily_tasks has user_id.
        return $this->hasMany(DailyTask::class);
    }

    public function calendarEvents(): HasMany
    {
        // One user can have many calendar event rows.
        // Laravel uses calendar_events.user_id to keep each student's calendar private.
        return $this->hasMany(CalendarEvent::class);
    }

    public function grades(): HasMany
    {
        // One student can have many grade rows.
        return $this->hasMany(Grade::class);
    }

    public function notes(): HasMany
    {
        // One student can create many private notes.
        return $this->hasMany(Note::class);
    }

    public function sentChatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canPostSchoolContent(): bool
    {
        // Later news and teacher chat tools can use this helper.
        return $this->isTeacher() || $this->isAdmin();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
