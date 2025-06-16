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
        Schema::table('user_funds', function (Blueprint $table) {
            // Rename value_in_dollars to amount and increase precision for crypto amounts
            $table->renameColumn('value_in_dollars', 'amount');
        });
        
        // In a separate schema call to handle precision change
        Schema::table('user_funds', function (Blueprint $table) {
            // Update precision to handle crypto amounts with 8 decimal places
            $table->decimal('amount', 18, 8)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_funds', function (Blueprint $table) {
            // Change back to original precision
            $table->decimal('amount', 10, 2)->change();
        });
        
        Schema::table('user_funds', function (Blueprint $table) {
            // Rename back to value_in_dollars
            $table->renameColumn('amount', 'value_in_dollars');
        });
    }
};
