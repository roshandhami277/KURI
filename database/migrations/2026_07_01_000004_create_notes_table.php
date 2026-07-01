<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            // user_id makes every note private to the student who created it.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // subject_id is optional because some notes may not belong to one subject.
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            // One simple tag for now. Later this can become a separate tags table if needed.
            $table->string('tag')->nullable();
            $table->string('description')->nullable();
            // body is the large writing area, like the inside of a document.
            $table->longText('body')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
