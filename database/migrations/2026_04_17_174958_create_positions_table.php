<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('position_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('slots_needed')->default(1);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->timestamps();

            $table->index(['event_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
