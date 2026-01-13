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
        Schema::create('document_shares', function (Blueprint $table) {
            $table->id();
            $table->string('document_type'); // 'task_attachment', 'project_file', etc.
            $table->unsignedBigInteger('document_id'); // ID of the document/file
            $table->foreignId('shared_with_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('shared_by_user_id')->constrained('users')->onDelete('cascade');
            $table->enum('permission', ['view', 'edit', 'download'])->default('view');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['document_type', 'document_id']);
            $table->index('shared_with_user_id');
            $table->index('expires_at');
            
            // Unique constraint: prevent duplicate shares
            $table->unique(['document_type', 'document_id', 'shared_with_user_id'], 'unique_document_share');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_shares');
    }
};
