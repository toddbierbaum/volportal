<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('requires_background_check')->default(false);
            $table->boolean('requires_age_certification')->default(false);
        });

        Schema::table('users', function (Blueprint $table) {
            // Nullable timestamps: null = not acknowledged / not approved yet.
            $table->timestamp('background_check_acknowledged_at')->nullable();
            $table->timestamp('age_certified_at')->nullable();
            $table->timestamp('approved_at')->nullable();
        });

        // Back-fill: everyone already in the system is considered approved
        // as of now. Only brand-new signups after this migration go through
        // the pending-review flow.
        DB::table('users')->update(['approved_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['background_check_acknowledged_at', 'age_certified_at', 'approved_at']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['requires_background_check', 'requires_age_certification']);
        });
    }
};
