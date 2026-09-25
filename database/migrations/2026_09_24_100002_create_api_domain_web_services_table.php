<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_domain_web_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('api_web_service_domains')->cascadeOnDelete();
            $table->foreignId('web_service_id')->constrained('api_web_services')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['domain_id', 'web_service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_domain_web_services');
    }
};
