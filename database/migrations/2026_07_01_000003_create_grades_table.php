<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            // user_id connects the grade to the student who added it.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // subject_id connects the grade to one subject.
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            // Optional title, for example "First test".
            $table->string('title')->nullable();
            // Portuguese school grades use 0 to 20.
            $table->decimal('grade', 5, 2);
            // The date the student got the grade.
            $table->date('grade_date')->nullable();
            // Optional small note.
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
