<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_subject', function (Blueprint $table) {
            $table->id();
            // One course can have many subjects.
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            // One subject can belong to many courses.
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // This stops the same subject being attached twice to the same course.
            $table->unique(['course_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_subject');
    }
};
