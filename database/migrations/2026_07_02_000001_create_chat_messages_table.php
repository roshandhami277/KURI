<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            // course_id tells us which course group this message belongs to.
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            // sender_id is the logged-in user who wrote the message.
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            // body can be empty when the user sends only an attachment.
            $table->text('body')->nullable();
            // These three columns save the uploaded file information, if the message has a file.
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
