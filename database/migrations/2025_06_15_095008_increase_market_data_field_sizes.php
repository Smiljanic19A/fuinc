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
        Schema::table('market_data', function (Blueprint $table) {
            // Increase volume fields to handle very large trading volumes
            $table->decimal('volume', 30, 8)->change();
            $table->decimal('quote_volume', 30, 8)->change();
            
            // Also increase price fields to handle edge cases
            $table->decimal('open', 20, 8)->change();
            $table->decimal('high', 20, 8)->change();
            $table->decimal('low', 20, 8)->change();
            $table->decimal('close', 20, 8)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_data', function (Blueprint $table) {
            // Revert to original sizes
            $table->decimal('volume', 20, 8)->change();
            $table->decimal('quote_volume', 20, 8)->change();
            $table->decimal('open', 16, 8)->change();
            $table->decimal('high', 16, 8)->change();
            $table->decimal('low', 16, 8)->change();
            $table->decimal('close', 16, 8)->change();
        });
    }
};
