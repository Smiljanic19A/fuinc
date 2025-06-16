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
        Schema::create('user_funds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('value_in_dollars', 20, 8); // USD value with high precision
            $table->string('currency', 10); // BTC, ETH, USDT, etc.
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['user_id', 'currency']);
            $table->index(['currency']);
            $table->unique(['user_id', 'currency']); // Prevent duplicate currency entries per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_funds');
    }
};
