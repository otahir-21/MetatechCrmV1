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
        Schema::table('companies', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['company_super_admin_id']);
            
            // Make the column nullable
            $table->unsignedBigInteger('company_super_admin_id')->nullable()->change();
            
            // Re-add the foreign key constraint (with nullable)
            $table->foreign('company_super_admin_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['company_super_admin_id']);
            
            // Make the column NOT NULL again
            // Note: This will fail if there are any NULL values in the column
            $table->unsignedBigInteger('company_super_admin_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint
            $table->foreign('company_super_admin_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
