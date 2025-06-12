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
        Schema::create('coins', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 20)->unique(); // BTC, ETH, USDT, etc.
            $table->string('name', 100); // Bitcoin, Ethereum, Tether
            $table->string('full_name', 255)->nullable(); // Bitcoin (BTC)
            $table->text('description')->nullable();
            $table->string('icon_url')->nullable();
            $table->string('website_url')->nullable();
            $table->string('whitepaper_url')->nullable();
            $table->decimal('current_price', 16, 8)->default(0);
            $table->decimal('market_cap', 20, 2)->nullable();
            $table->decimal('volume_24h', 20, 8)->default(0);
            $table->decimal('price_change_24h', 16, 8)->default(0);
            $table->decimal('price_change_percentage_24h', 8, 4)->default(0);
            $table->integer('market_cap_rank')->nullable();
            $table->decimal('circulating_supply', 20, 8)->nullable();
            $table->decimal('total_supply', 20, 8)->nullable();
            $table->decimal('max_supply', 20, 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_hot')->default(false); // Hot coin flag
            $table->boolean('is_new')->default(false); // New coin flag
            $table->boolean('is_trending')->default(false); // Trending coin flag
            $table->string('category', 100)->nullable(); // DeFi, NFT, Metaverse, etc.
            $table->string('blockchain', 50)->nullable(); // Ethereum, BSC, Polygon, etc.
            $table->json('tags')->nullable(); // Array of tags
            $table->timestamp('launched_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['is_active', 'is_hot']);
            $table->index(['is_active', 'is_new']);
            $table->index(['is_active', 'is_trending']);
            $table->index(['symbol']);
            $table->index(['market_cap_rank']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coins');
    }
};
