<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['confirmed', 'waitlisted', 'cancelled', 'attended', 'no_show'])
                ->default('confirmed');
            $table->decimal('hours_worked', 5, 2)->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'position_id']);
            $table->index(['position_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signups');
    }
};
