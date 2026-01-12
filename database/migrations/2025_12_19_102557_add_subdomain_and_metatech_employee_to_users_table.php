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
            $table->string('subdomain', 255)->nullable()->after('company_name');
            $table->boolean('is_metatech_employee')->default(false)->after('subdomain');
            
            // Add index for faster lookups
            $table->index('subdomain');
            $table->index('is_metatech_employee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['subdomain']);
            $table->dropIndex(['is_metatech_employee']);
            $table->dropColumn(['subdomain', 'is_metatech_employee']);
        });
    }
};
