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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('market_id')->constrained()->onDelete('cascade');
            $table->string('order_id', 36)->unique(); // UUID for external reference
            $table->enum('type', ['market', 'limit', 'stop_loss', 'take_profit']);
            $table->enum('side', ['buy', 'sell']);
            $table->decimal('quantity', 20, 8); // Amount to buy/sell
            $table->decimal('price', 16, 8)->nullable(); // Null for market orders
            $table->decimal('filled_quantity', 20, 8)->default(0);
            $table->decimal('remaining_quantity', 20, 8)->default(0);
            $table->decimal('average_price', 16, 8)->default(0);
            $table->decimal('total_value', 20, 8)->default(0); // Total value of order
            $table->decimal('fee_amount', 16, 8)->default(0);
            $table->string('fee_currency', 10)->default('USDT');
            $table->enum('status', ['pending', 'partially_filled', 'filled', 'cancelled', 'rejected'])->default('pending');
            $table->decimal('stop_price', 16, 8)->nullable(); // For stop orders
            $table->decimal('trigger_price', 16, 8)->nullable(); // For conditional orders
            $table->enum('time_in_force', ['GTC', 'IOC', 'FOK'])->default('GTC'); // Good Till Cancel, Immediate or Cancel, Fill or Kill
            $table->boolean('is_admin_created')->default(false); // For admin-created fake orders
            $table->timestamp('executed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->json('metadata')->nullable(); // Additional order data
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['market_id', 'status']);
            $table->index(['order_id']);
            $table->index(['side', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
