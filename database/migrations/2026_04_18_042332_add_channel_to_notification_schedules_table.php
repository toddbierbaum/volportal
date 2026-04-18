<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_schedules', function (Blueprint $table) {
            $table->enum('channel', ['email', 'sms', 'both'])->default('email')->after('offset_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('notification_schedules', function (Blueprint $table) {
            $table->dropColumn('channel');
        });
    }
};
