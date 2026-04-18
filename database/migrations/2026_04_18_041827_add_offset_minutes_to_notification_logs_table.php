<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->unsignedInteger('offset_minutes')->nullable()->after('notification_schedule_id');
            $table->index(['signup_id', 'offset_minutes']);
        });

        // Backfill: any existing log row whose schedule still exists gets
        // its offset_minutes copied over so the dedup check keeps working.
        DB::statement('UPDATE notification_logs SET offset_minutes = (
            SELECT offset_minutes FROM notification_schedules WHERE notification_schedules.id = notification_logs.notification_schedule_id
        ) WHERE notification_schedule_id IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropIndex(['signup_id', 'offset_minutes']);
            $table->dropColumn('offset_minutes');
        });
    }
};
