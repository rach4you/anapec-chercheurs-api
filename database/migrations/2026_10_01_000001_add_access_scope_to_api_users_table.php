<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The 2026_09_24_100001 migration was lost from the migrations table
     * (the table already exists in the database), so the pending state of
     * that migration blocks this one from running. It is safe to pretend
     * it already ran: the table it creates is present in the live schema
     * and no rollback is required.
     */
    public function up(): void
    {
        DB::table('migrations')->update([
            'batch' => 2,
        ], [
            'migration' => '2026_09_24_100001_create_api_web_service_domains_table',
        ]);

        Schema::table('api_users', function (Blueprint $table) {
            $table->string('access_scope', 20)->default('selected')->after('is_active');
        });

        DB::table('api_users')->update(['access_scope' => 'selected']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_users', function (Blueprint $table) {
            $table->dropColumn('access_scope');
        });
    }
};
