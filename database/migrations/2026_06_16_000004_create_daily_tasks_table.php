<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_tasks', function (Blueprint $table) {
            $table->id();
            // user_id connects each task to one account in the users table.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // title is the text the student writes.
            $table->string('title');
            // task_date lets the same student have different tasks on different days.
            $table->date('task_date');
            // null means incomplete. A date/time means completed.
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_tasks');
    }
};
