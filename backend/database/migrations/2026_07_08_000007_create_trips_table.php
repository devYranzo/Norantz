<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->string('trip_id')->primary();
            $table->string('route_id');
            $table->string('service_id');
            $table->string('shape_id')->nullable();
            $table->string('headsign')->nullable();
            $table->tinyInteger('direction_id')->nullable(); // 0 o 1
            $table->timestamps();

            $table->foreign('route_id')->references('route_id')->on('routes')->cascadeOnDelete();
            $table->foreign('service_id')->references('service_id')->on('calendars')->cascadeOnDelete();
            $table->foreign('shape_id')->references('shape_id')->on('shapes')->nullOnDelete();

            $table->index('route_id');
            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
