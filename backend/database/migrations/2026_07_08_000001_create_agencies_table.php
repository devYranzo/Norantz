<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agencies', function (Blueprint $table) {
            // Usamos el agency_id de GTFS tal cual como clave primaria,
            // así el import no tiene que resolver ids internos.
            $table->string('agency_id')->primary();
            $table->string('name');
            $table->string('url')->nullable();
            $table->string('timezone')->default('Europe/Madrid');
            $table->string('lang')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
};
