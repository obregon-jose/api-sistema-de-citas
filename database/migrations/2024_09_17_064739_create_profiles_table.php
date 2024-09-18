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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // $table->foreignId('role_id')->default(1)->constrained()->onDelete('cascade');
            $table->timestamps();
            // Establecer una restricción de clave única compuesta
            $table->unique(['user_id', 'role_id']); // user_id y role_id deben ser únicos en combinación
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
