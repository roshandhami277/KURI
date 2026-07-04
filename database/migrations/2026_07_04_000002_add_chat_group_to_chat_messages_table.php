<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            // course_id can be empty when the message belongs to a teacher group instead.
            $table->foreignId('course_id')->nullable()->change();
            $table->foreignId('chat_group_id')->nullable()->after('course_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('chat_group_id');
            $table->foreignId('course_id')->nullable(false)->change();
        });
    }
};
