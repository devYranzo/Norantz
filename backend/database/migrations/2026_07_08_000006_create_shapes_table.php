<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shapes', function (Blueprint $table) {
            $table->string('shape_id')->primary();
            // Array de puntos [ [lat, lng], [lat, lng], ... ] ya ordenados,
            // coincide con TransitRoute.coordinates en tu frontend.
            $table->json('coordinates');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shapes');
    }
};
