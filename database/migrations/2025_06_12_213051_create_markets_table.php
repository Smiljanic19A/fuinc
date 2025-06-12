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
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 20)->unique(); // e.g., 'BTCUSDT'
            $table->string('base_currency', 10); // e.g., 'BTC'
            $table->string('quote_currency', 10); // e.g., 'USDT'
            $table->string('display_name', 50); // e.g., 'Bitcoin/USDT'
            $table->decimal('current_price', 16, 8)->default(0);
            $table->decimal('price_change_24h', 16, 8)->default(0);
            $table->decimal('price_change_percentage_24h', 8, 4)->default(0);
            $table->decimal('high_24h', 16, 8)->default(0);
            $table->decimal('low_24h', 16, 8)->default(0);
            $table->decimal('volume_24h', 20, 8)->default(0);
            $table->decimal('market_cap', 20, 2)->nullable();
            $table->integer('rank')->nullable();
            $table->decimal('min_order_amount', 16, 8)->default(0.00000001);
            $table->decimal('max_order_amount', 20, 8)->default(999999999);
            $table->integer('price_precision')->default(8);
            $table->integer('quantity_precision')->default(8);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_trading_enabled')->default(true);
            $table->string('icon_url')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['is_active', 'is_trading_enabled']);
            $table->index('symbol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('markets');
    }
};
