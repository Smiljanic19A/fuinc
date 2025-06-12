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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('content');
            $table->enum('type', ['info', 'warning', 'success', 'danger'])->default('info');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sticky')->default(false); // Pin to top
            $table->boolean('show_on_homepage')->default(false);
            $table->boolean('show_in_dashboard')->default(true);
            $table->boolean('send_notification')->default(false); // Send push notification
            $table->string('target_audience', 50)->default('all'); // all, users, superadmins
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->string('image_url')->nullable();
            $table->string('action_url')->nullable(); // Link to action
            $table->string('action_text', 100)->nullable(); // Action button text
            $table->integer('view_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['is_active', 'published_at']);
            $table->index(['type', 'priority']);
            $table->index(['target_audience']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
