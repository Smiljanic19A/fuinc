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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('transaction_id', 36)->unique(); // UUID for external reference
            $table->enum('type', ['deposit', 'withdrawal']);
            $table->string('currency', 10); // BTC, ETH, USDT, etc.
            $table->decimal('amount', 20, 8); // Transaction amount
            $table->decimal('fee_amount', 16, 8)->default(0); // Transaction fee
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled', 'failed'])->default('pending');
            $table->string('wallet_address', 255)->nullable(); // Blockchain address
            $table->string('transaction_hash', 255)->nullable(); // Blockchain transaction hash
            $table->string('network', 50)->nullable(); // Network type (ERC20, TRC20, etc.)
            $table->integer('confirmations')->default(0); // Blockchain confirmations
            $table->integer('required_confirmations')->default(6); // Required confirmations
            $table->text('admin_notes')->nullable(); // Admin notes
            $table->boolean('is_fake')->default(true); // Mark as fake transaction
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Admin who approved
            $table->text('cancel_reason')->nullable();
            $table->json('metadata')->nullable(); // Additional transaction data
            $table->timestamps();
            
            $table->index(['user_id', 'type', 'status']);
            $table->index(['transaction_id']);
            $table->index(['currency', 'status']);
            $table->index(['wallet_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
