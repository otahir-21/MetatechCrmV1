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
        // Check if password_reset_tokens already exists (from default Laravel migration)
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // Add columns for single-use and expiration tracking
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            if (!Schema::hasColumn('password_reset_tokens', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('email');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            }
            if (!Schema::hasColumn('password_reset_tokens', 'used')) {
                $table->boolean('used')->default(false)->after('token');
            }
            if (!Schema::hasColumn('password_reset_tokens', 'used_at')) {
                $table->timestamp('used_at')->nullable()->after('used');
            }
            if (!Schema::hasColumn('password_reset_tokens', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('created_at');
            }
            if (!Schema::hasColumn('password_reset_tokens', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('expires_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            if (Schema::hasColumn('password_reset_tokens', 'ip_address')) {
                $table->dropColumn('ip_address');
            }
            if (Schema::hasColumn('password_reset_tokens', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
            if (Schema::hasColumn('password_reset_tokens', 'used_at')) {
                $table->dropColumn('used_at');
            }
            if (Schema::hasColumn('password_reset_tokens', 'used')) {
                $table->dropColumn('used');
            }
            if (Schema::hasColumn('password_reset_tokens', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
