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
        Schema::table('projects', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['company_id']);
            // Make company_id nullable for internal projects
            $table->unsignedBigInteger('company_id')->nullable()->change();
            // Re-add foreign key constraint
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['company_id']);
            // Make company_id required again
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            // Re-add foreign key
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }
};
