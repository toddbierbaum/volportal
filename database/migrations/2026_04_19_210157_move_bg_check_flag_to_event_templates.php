<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // BG check requirement is an event-type concern, not a category
        // concern. ANY position on a Kids Production event needs the
        // volunteer cleared, regardless of whether they're House Manager,
        // Concessions, Door, etc. Moving the flag to event_templates.

        Schema::table('event_templates', function (Blueprint $table) {
            $table->boolean('requires_background_check')->default(false);
        });

        DB::table('event_templates')
            ->where('slug', 'kids-production')
            ->update(['requires_background_check' => true]);

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('requires_background_check');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('requires_background_check')->default(false);
        });

        Schema::table('event_templates', function (Blueprint $table) {
            $table->dropColumn('requires_background_check');
        });
    }
};
