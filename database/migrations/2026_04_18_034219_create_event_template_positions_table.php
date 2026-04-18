<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_template_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('slots_needed')->default(1);
            $table->boolean('is_public')->default(true);
            $table->unsignedInteger('call_offset_minutes')->default(60);
            $table->unsignedInteger('duration_minutes')->default(180);
            $table->unsignedInteger('position_order')->default(0);
            $table->timestamps();

            $table->index(['event_template_id', 'position_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_template_positions');
    }
};
