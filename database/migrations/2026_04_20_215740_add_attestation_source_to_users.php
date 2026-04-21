<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('age_certified_via')->nullable()->after('age_certified_at');
            $table->string('background_check_acknowledged_via')->nullable()->after('background_check_acknowledged_at');
        });

        // Any pre-existing attestations predate source tracking. Per Todd:
        // mark them as admin_intake so the display never claims a user
        // certified "via signup form" when we can't prove that was true.
        DB::table('users')->whereNotNull('age_certified_at')
            ->update(['age_certified_via' => 'admin_intake']);
        DB::table('users')->whereNotNull('background_check_acknowledged_at')
            ->update(['background_check_acknowledged_via' => 'admin_intake']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['age_certified_via', 'background_check_acknowledged_via']);
        });
    }
};
