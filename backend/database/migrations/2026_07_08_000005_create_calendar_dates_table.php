<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_dates', function (Blueprint $table) {
            $table->id();
            $table->string('service_id');
            $table->date('date');
            // 1 = servicio añadido ese día, 2 = servicio quitado ese día
            $table->tinyInteger('exception_type');
            $table->timestamps();

            $table->foreign('service_id')->references('service_id')->on('calendars')->cascadeOnDelete();
            $table->unique(['service_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_dates');
    }
};
