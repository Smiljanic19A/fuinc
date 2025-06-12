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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('market_id')->constrained()->onDelete('cascade');
            $table->string('position_id', 36)->unique(); // UUID for external reference
            $table->enum('side', ['long', 'short']); // Position direction
            $table->decimal('entry_price', 16, 8); // Average entry price
            $table->decimal('current_price', 16, 8)->default(0); // Current market price
            $table->decimal('quantity', 20, 8); // Position size
            $table->decimal('remaining_quantity', 20, 8); // Remaining open quantity
            $table->decimal('margin_used', 20, 8)->default(0); // Margin used for position
            $table->decimal('leverage', 8, 2)->default(1.00); // Leverage multiplier
            $table->decimal('unrealized_pnl', 20, 8)->default(0); // Unrealized profit/loss
            $table->decimal('realized_pnl', 20, 8)->default(0); // Realized profit/loss
            $table->decimal('total_fees', 16, 8)->default(0); // Total fees paid
            $table->decimal('stop_loss_price', 16, 8)->nullable(); // Stop loss price
            $table->decimal('take_profit_price', 16, 8)->nullable(); // Take profit price
            $table->decimal('liquidation_price', 16, 8)->nullable(); // Liquidation price
            $table->enum('status', ['open', 'closed', 'liquidated'])->default('open');
            $table->boolean('is_admin_created')->default(false); // For admin-created fake positions
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->text('close_reason')->nullable(); // Reason for closing
            $table->json('metadata')->nullable(); // Additional position data
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['market_id', 'status']);
            $table->index(['position_id']);
            $table->index(['side', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
