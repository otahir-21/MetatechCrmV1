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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable(); // Rich text description (like Notion)
            $table->enum('status', ['todo', 'in_progress', 'review', 'done', 'archived'])->default('todo');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('due_date')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('position')->default(0); // For drag-and-drop ordering (Notion-like)
            $table->json('tags')->nullable(); // Array of tags (like Notion)
            $table->json('checklist')->nullable(); // Array of checklist items (like Notion)
            $table->json('attachments')->nullable(); // Array of file attachments
            $table->boolean('is_pinned')->default(false); // Pin important tasks (like Notion)
            $table->timestamps();
            
            $table->index('project_id');
            $table->index('assigned_to');
            $table->index('status');
            $table->index('priority');
            $table->index('due_date');
            $table->index('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
