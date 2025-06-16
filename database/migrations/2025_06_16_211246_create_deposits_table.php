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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 20, 8); // Supports crypto precision
            $table->string('currency', 10); // BTC, ETH, USDT, etc.
            $table->string('network', 50); // Bitcoin, Ethereum, BSC, etc.
            $table->string('address')->nullable(); // Wallet address - nullable by default
            $table->boolean('filled')->default(false); // Whether deposit is completed
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['currency', 'filled']);
            $table->index(['address']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
