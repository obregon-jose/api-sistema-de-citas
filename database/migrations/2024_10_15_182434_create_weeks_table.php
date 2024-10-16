<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeksTable extends Migration
{
    public function up()
    {
        Schema::create('weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_id')->constrained()->onDelete('cascade'); // Relación con agendas
            $table->date('fecha_inicio'); // Fecha de inicio de la semana
            $table->date('fecha_fin'); // Fecha de fin de la semana
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('weeks'); // Eliminar la tabla al revertir la migración
    }
}
