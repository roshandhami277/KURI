<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            // user_id connects the event to the student who created it.
            // This is why every student only sees their own calendar events.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // The title is the main event text, for example "Math test".
            $table->string('title');
            // The type helps us group events, for example Homework, Test, Exam, Presentation.
            $table->string('type')->default('Other');
            // The event_date decides which calendar square the event appears on.
            $table->date('event_date');
            // start_time is the time shown in the day timeline.
            $table->time('start_time');
            // end_time is optional because sometimes the student only knows the start time.
            $table->time('end_time')->nullable();
            // notes are optional extra details.
            $table->text('notes')->nullable();
            // This only stores whether the student wants an email reminder.
            // The actual email sending will be built later.
            $table->boolean('reminder_enabled')->default(false);
            // This stores the time the student wants to be reminded.
            $table->time('reminder_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
