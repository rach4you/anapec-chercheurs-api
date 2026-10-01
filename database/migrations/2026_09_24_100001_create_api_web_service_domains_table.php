<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Idempotent: the table already exists in the live database but its
     * "ran" row was lost from the migrations table, so re-running is a
     * no-op instead of a failing CREATE.
     */
    public function up(): void
    {
        if (Schema::hasTable('api_web_service_domains')) {
            return;
        }

        Schema::create('api_web_service_domains', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('name', 255);
            $table->string('description', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_web_service_domains');
    }
};
