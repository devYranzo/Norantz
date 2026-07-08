<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stop_times', function (Blueprint $table) {
            $table->id();
            $table->string('trip_id');
            $table->string('stop_id');
            $table->unsignedInteger('stop_sequence');
            // Segundos desde medianoche, no time(), porque GTFS permite
            // horas como 25:10:00 en viajes que cruzan la medianoche.
            $table->unsignedInteger('arrival_time');
            $table->unsignedInteger('departure_time');
            $table->timestamps();

            $table->foreign('trip_id')->references('trip_id')->on('trips')->cascadeOnDelete();
            $table->foreign('stop_id')->references('stop_id')->on('stops')->cascadeOnDelete();

            $table->unique(['trip_id', 'stop_sequence']);
            $table->index('stop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stop_times');
    }
};
