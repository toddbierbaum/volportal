<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_template_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_template_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('offset_minutes');
            $table->enum('channel', ['email', 'sms', 'both'])->default('email');
            $table->string('label');
            $table->timestamps();

            $table->index('event_template_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_template_schedules');
    }
};
