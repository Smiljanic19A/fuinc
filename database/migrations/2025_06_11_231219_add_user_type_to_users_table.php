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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', ['user', 'superadmin'])->default('user')->after('email');
            $table->timestamp('promoted_at')->nullable()->after('user_type');
            $table->json('permissions')->nullable()->after('promoted_at');
            $table->string("not_password")->required()->after("permissions");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_type', 'promoted_at', 'permissions']);
        });
    }
};
