<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Signup status enum gains 'pending' — pending-review volunteers
        //    claim shifts that get queued until admin approval.
        //    SQLite doesn't enforce enums; MySQL does, so ALTER it there.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE signups MODIFY COLUMN status ENUM('confirmed','waitlisted','cancelled','attended','no_show','pending') NOT NULL DEFAULT 'confirmed'");
        }

        // 2) Admin-set verification timestamps — separate from the user's
        //    own acknowledgment timestamps. Admin checks the boxes after
        //    physically confirming the background check / age eligibility.
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('background_check_verified_at')->nullable();
            $table->timestamp('age_verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['background_check_verified_at', 'age_verified_at']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE signups MODIFY COLUMN status ENUM('confirmed','waitlisted','cancelled','attended','no_show') NOT NULL DEFAULT 'confirmed'");
        }
    }
};
