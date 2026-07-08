<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stops', function (Blueprint $table) {
            $table->string('stop_id')->primary();
            $table->string('agency_id')->nullable();
            $table->string('region_id')->nullable();
            $table->string('name');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            // Modos que pasan por esta parada, ej. ["bus", "tram"]
            $table->json('modes')->nullable();
            $table->timestamps();

            $table->foreign('agency_id')->references('agency_id')->on('agencies')->nullOnDelete();

            // Índice compuesto para acelerar el filtrado por bounding box
            // que ya haces en useVisibleStops.
            $table->index(['latitude', 'longitude']);
            $table->index('region_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stops');
    }
};
