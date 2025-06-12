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
        Schema::create('market_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained()->onDelete('cascade');
            $table->string('timeframe', 10); // 1m, 5m, 15m, 1h, 4h, 1d, 1w, 1M
            $table->timestamp('timestamp'); // Candle timestamp
            $table->decimal('open', 16, 8); // Opening price
            $table->decimal('high', 16, 8); // Highest price
            $table->decimal('low', 16, 8); // Lowest price
            $table->decimal('close', 16, 8); // Closing price
            $table->decimal('volume', 20, 8); // Trading volume
            $table->decimal('quote_volume', 20, 8); // Quote asset volume
            $table->integer('trades_count')->default(0); // Number of trades
            $table->boolean('is_fake')->default(true); // Mark as fake data
            $table->boolean('is_closed')->default(false); // Is candle closed/complete
            $table->timestamps();
            
            $table->unique(['market_id', 'timeframe', 'timestamp']);
            $table->index(['market_id', 'timeframe', 'timestamp']);
            $table->index(['timestamp', 'timeframe']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_data');
    }
};
