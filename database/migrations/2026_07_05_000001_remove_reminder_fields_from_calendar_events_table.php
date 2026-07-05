<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            if (Schema::hasColumn('calendar_events', 'reminder_enabled')) {
                $table->dropColumn('reminder_enabled');
            }

            if (Schema::hasColumn('calendar_events', 'reminder_time')) {
                $table->dropColumn('reminder_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            if (! Schema::hasColumn('calendar_events', 'reminder_enabled')) {
                $table->boolean('reminder_enabled')->default(false);
            }

            if (! Schema::hasColumn('calendar_events', 'reminder_time')) {
                $table->time('reminder_time')->nullable();
            }
        });
    }
};
