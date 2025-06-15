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
        Schema::table('markets', function (Blueprint $table) {
            // Increase volume and market cap fields for large values
            $table->decimal('volume_24h', 30, 8)->default(0)->change();
            $table->decimal('market_cap', 30, 2)->nullable()->change();
            
            // Increase price fields
            $table->decimal('current_price', 20, 8)->default(0)->change();
            $table->decimal('price_change_24h', 20, 8)->default(0)->change();
            $table->decimal('high_24h', 20, 8)->default(0)->change();
            $table->decimal('low_24h', 20, 8)->default(0)->change();
            
            // Increase order amount fields
            $table->decimal('min_order_amount', 20, 8)->default(0.00000001)->change();
            $table->decimal('max_order_amount', 30, 8)->default(999999999)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('markets', function (Blueprint $table) {
            // Revert to original sizes
            $table->decimal('volume_24h', 20, 8)->default(0)->change();
            $table->decimal('market_cap', 20, 2)->nullable()->change();
            $table->decimal('current_price', 16, 8)->default(0)->change();
            $table->decimal('price_change_24h', 16, 8)->default(0)->change();
            $table->decimal('high_24h', 16, 8)->default(0)->change();
            $table->decimal('low_24h', 16, 8)->default(0)->change();
            $table->decimal('min_order_amount', 16, 8)->default(0.00000001)->change();
            $table->decimal('max_order_amount', 20, 8)->default(999999999)->change();
        });
    }
};
