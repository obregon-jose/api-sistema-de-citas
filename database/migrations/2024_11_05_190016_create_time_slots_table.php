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
        // Schema::create('time_slots', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('day_id')->constrained()->onDelete('cascade');
        //     $table->time('hour_start');
        //     $table->time('hour_end');
        //     $table->boolean('available')->default(true);
        //     $table->timestamps();
        //     $table->unique(['day_id', 'hour_start','hour_end']);
        // });

        /*---------- NUEVA MIGRACIÓN ---------*/
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->time('hour_start');
            $table->time('hour_end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
