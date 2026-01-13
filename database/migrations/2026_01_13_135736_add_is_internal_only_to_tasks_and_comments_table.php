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
        // Add is_internal_only to tasks table
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('is_internal_only')->default(false)->after('status');
            $table->index('is_internal_only');
        });

        // Add is_internal_only to task_comments table
        Schema::table('task_comments', function (Blueprint $table) {
            $table->boolean('is_internal_only')->default(false)->after('comment');
            $table->index('is_internal_only');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['is_internal_only']);
            $table->dropColumn('is_internal_only');
        });

        Schema::table('task_comments', function (Blueprint $table) {
            $table->dropIndex(['is_internal_only']);
            $table->dropColumn('is_internal_only');
        });
    }
};
