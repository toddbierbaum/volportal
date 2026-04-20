<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Earlier migration (2026_04_19_202633) only extended the enum on
        // MySQL because I assumed SQLite didn't enforce enums. It does —
        // via a CHECK constraint — so 'pending' inserts fail in prod.
        // Rebuild the column via change() so SQLite regenerates the table
        // with the wider constraint. MySQL already has 'pending' so skip it.
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('signups', function (Blueprint $table) {
                $table->enum('status', ['confirmed', 'waitlisted', 'cancelled', 'attended', 'no_show', 'pending'])
                    ->default('confirmed')
                    ->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('signups', function (Blueprint $table) {
                $table->enum('status', ['confirmed', 'waitlisted', 'cancelled', 'attended', 'no_show'])
                    ->default('confirmed')
                    ->change();
            });
        }
    }
};
