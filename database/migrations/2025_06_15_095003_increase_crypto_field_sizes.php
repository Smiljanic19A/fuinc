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
        Schema::table('coins', function (Blueprint $table) {
            // Increase supply fields to handle tokens like SHIB with trillions of supply
            $table->decimal('circulating_supply', 30, 8)->nullable()->change();
            $table->decimal('total_supply', 30, 8)->nullable()->change();
            $table->decimal('max_supply', 30, 8)->nullable()->change();
            
            // Increase market cap and volume fields for large values
            $table->decimal('market_cap', 30, 2)->nullable()->change();
            $table->decimal('volume_24h', 30, 8)->default(0)->change();
            
            // Increase price change fields
            $table->decimal('price_change_24h', 20, 8)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coins', function (Blueprint $table) {
            // Revert to original sizes
            $table->decimal('circulating_supply', 20, 8)->nullable()->change();
            $table->decimal('total_supply', 20, 8)->nullable()->change();
            $table->decimal('max_supply', 20, 8)->nullable()->change();
            $table->decimal('market_cap', 20, 2)->nullable()->change();
            $table->decimal('volume_24h', 20, 8)->default(0)->change();
            $table->decimal('price_change_24h', 16, 8)->default(0)->change();
        });
    }
};
