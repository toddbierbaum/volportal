<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->unsignedInteger('offset_minutes');
            $table->timestamps();

            $table->index('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_schedules');
    }
};
