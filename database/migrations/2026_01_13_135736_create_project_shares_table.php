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
        Schema::create('project_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('resource_type'); // 'task', 'comment', 'file', 'milestone', etc.
            $table->unsignedBigInteger('resource_id'); // ID of the specific resource
            $table->foreignId('shared_with_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('shared_by_user_id')->constrained('users')->onDelete('cascade');
            $table->enum('permission', ['view', 'comment', 'edit'])->default('view');
            $table->text('notes')->nullable(); // Admin can add notes about why sharing
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['project_id', 'shared_with_user_id']);
            $table->index(['resource_type', 'resource_id']);
            $table->index('expires_at');
            
            // Unique constraint: prevent duplicate shares
            $table->unique(['resource_type', 'resource_id', 'shared_with_user_id'], 'unique_project_resource_share');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_shares');
    }
};
