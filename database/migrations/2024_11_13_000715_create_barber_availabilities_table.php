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
        Schema::create('barber_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            
            // $table->foreignId('day_id')->constrained('availability_days')->onDelete('cascade');
            $table->json('time_slot_id')->constrained('time_slots')->onDelete('cascade');
            //$table->boolean('status')->default(false); // Por defecto, inactivo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barber_availabilities');
    }
};
