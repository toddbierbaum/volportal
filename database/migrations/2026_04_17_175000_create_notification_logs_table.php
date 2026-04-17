<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signup_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notification_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['signup_id', 'notification_schedule_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
