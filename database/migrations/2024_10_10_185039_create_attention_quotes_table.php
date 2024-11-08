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
        Schema::create('attention_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('client_name')->nullable();
            $table->foreignId('barber_id')->constrained('profiles', 'user_id')->onDelete('cascade');
            $table->json('service_details');
            $table->integer('total_paid');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending'); 
            $table->timestamps(); //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attention_quotes');
    }
};
