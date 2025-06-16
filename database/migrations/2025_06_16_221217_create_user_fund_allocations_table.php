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
        Schema::create('user_fund_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->string('currency', 10); // Currency being allocated
            $table->decimal('amount', 20, 8); // Amount allocated
            $table->enum('type', ['order_reserve', 'margin_used'])->default('order_reserve');
            $table->boolean('is_active')->default(true); // Whether allocation is still active
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'currency', 'is_active']);
            $table->index(['order_id']);
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_fund_allocations');
    }
};
