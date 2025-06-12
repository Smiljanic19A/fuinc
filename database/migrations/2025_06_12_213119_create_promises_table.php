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
        Schema::create('promises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('promise_id', 36)->unique(); // UUID for external reference
            $table->enum('type', ['bonus', 'credit', 'referral', 'promotion', 'cashback', 'reward']);
            $table->string('title', 255);
            $table->text('description');
            $table->decimal('amount', 20, 8); // Promise amount
            $table->string('currency', 10)->default('USDT'); // Currency of promise
            $table->enum('status', ['pending', 'active', 'redeemed', 'expired', 'cancelled'])->default('pending');
            $table->decimal('redeemed_amount', 20, 8)->default(0); // Amount already used
            $table->decimal('remaining_amount', 20, 8)->default(0); // Amount remaining
            $table->json('redemption_conditions')->nullable(); // Conditions to redeem
            $table->integer('minimum_trades')->nullable(); // Min trades required
            $table->decimal('minimum_volume', 20, 8)->nullable(); // Min trading volume required
            $table->integer('validity_days')->nullable(); // Days until expiration
            $table->boolean('is_transferable')->default(false); // Can be transferred to other users
            $table->boolean('auto_apply')->default(true); // Auto apply when conditions met
            $table->string('referral_code', 50)->nullable(); // Associated referral code
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->foreignId('created_by')->constrained('users'); // Admin who created
            $table->text('admin_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['promise_id']);
            $table->index(['type', 'status']);
            $table->index(['referral_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promises');
    }
};
