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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 50); // 'login', 'invitation', 'role_change'
            $table->string('action', 50); // 'login_success', 'login_failed', 'invitation_sent', 'invitation_accepted', 'invitation_cancelled', 'role_updated'
            $table->unsignedBigInteger('user_id')->nullable(); // Who performed the action
            $table->unsignedBigInteger('target_user_id')->nullable(); // Target user (for role changes, invitations)
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('details')->nullable(); // Additional event-specific data
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes for performance
            $table->index('event_type');
            $table->index('action');
            $table->index('user_id');
            $table->index('target_user_id');
            $table->index('created_at');
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('target_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
