<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Data backfill: the DatabaseSeeder assigns these flags, but seeds
        // don't run on deploy. Apply them here so prod matches intent.
        DB::table('categories')
            ->where('slug', 'concessions')
            ->update(['requires_age_certification' => true]);

        // Ensure the Kids Productions category exists + is flagged for
        // background check. Idempotent — only inserts if missing.
        $existing = DB::table('categories')->where('slug', 'kids-productions')->first();
        if (! $existing) {
            DB::table('categories')->insert([
                'name' => 'Kids Productions',
                'slug' => 'kids-productions',
                'color' => '#EC4899',
                'description' => 'Volunteering at our youth performances and workshops.',
                'requires_background_check' => true,
                'requires_age_certification' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('categories')
                ->where('slug', 'kids-productions')
                ->update(['requires_background_check' => true]);
        }
    }

    public function down(): void
    {
        DB::table('categories')
            ->where('slug', 'concessions')
            ->update(['requires_age_certification' => false]);
        DB::table('categories')
            ->where('slug', 'kids-productions')
            ->update(['requires_background_check' => false]);
    }
};
