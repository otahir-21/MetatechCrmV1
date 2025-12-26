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
        Schema::create('bootstrap_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action', 50)->notNull(); // 'create', 'confirm', 'status_check'
            $table->string('result', 20)->notNull(); // 'success', 'failure'
            $table->string('ip_address', 45)->notNull();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email', 255)->nullable();
            $table->json('request_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('action');
            $table->index('result');
            $table->index('created_at');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bootstrap_audit_logs');
    }
};
