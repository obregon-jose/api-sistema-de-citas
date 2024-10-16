<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimeSlotsTable extends Migration
{
    public function up()
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_id')->unique()->constrained()->onDelete('cascade'); // Relación con semanas
            $table->string('dia');       // Día de la semana (Lunes, Martes, etc.)
            $table->time('inicio');      // Hora de inicio
            $table->time('fin');         // Hora de fin
            $table->boolean('estado')->default(true); // Estado de la franja
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('time_slots'); // Eliminar la tabla al revertir la migración
    }
}
