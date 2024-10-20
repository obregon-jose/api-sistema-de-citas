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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('profiles', 'user_id')->onDelete('cascade');
            $table->date('date');
            $table->time('time');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->string('note')->nullable();
            $table->foreignId('quote_id')->constrained('attention_quotes', 'id')->onDelete('cascade');
            // $table->unique(['date', 'time']);
            $table->unique(['client_id', 'date', 'time']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
