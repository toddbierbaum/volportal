<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categories can now optionally link to an event template. Picking
        // a linked category during volunteer signup means "I'm interested
        // in events of this template" — matching returns all positions on
        // those events regardless of position category. Without a link,
        // category matching works as before (positions with matching
        // category_id).
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('event_template_id')
                ->nullable()
                ->after('color')
                ->constrained('event_templates')
                ->nullOnDelete();
        });

        // Backfill: Kids Productions category links to the Kids Production
        // event template so the interest + matching + BG trigger all work
        // together the way Todd expects.
        $kidsTemplateId = DB::table('event_templates')
            ->where('slug', 'kids-production')
            ->value('id');

        if ($kidsTemplateId) {
            DB::table('categories')
                ->where('slug', 'kids-productions')
                ->update(['event_template_id' => $kidsTemplateId]);
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_template_id');
        });
    }
};
