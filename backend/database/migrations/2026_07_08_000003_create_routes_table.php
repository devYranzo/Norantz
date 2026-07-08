<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->string('route_id')->primary();
            $table->string('agency_id')->nullable();
            $table->string('region_id')->nullable();
            $table->string('short_name')->nullable();
            $table->string('long_name')->nullable();
            $table->string('color', 7)->nullable(); // ej. "#FF6600"
            $table->enum('mode', ['bus', 'tram', 'night_bus'])->default('bus');
            $table->timestamps();

            $table->foreign('agency_id')->references('agency_id')->on('agencies')->nullOnDelete();
            $table->index('mode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
