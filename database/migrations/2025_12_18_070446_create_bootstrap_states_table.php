<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bootstrap_states', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['BOOTSTRAP_PENDING', 'BOOTSTRAP_CONFIRMED', 'ACTIVE'])
                ->default('BOOTSTRAP_PENDING')
                ->notNull();
            $table->string('super_admin_email', 255)->nullable();
            $table->unsignedBigInteger('super_admin_id')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('super_admin_id')->references('id')->on('users')->onDelete('set null');
        });
        
        // Insert initial bootstrap state
        DB::table('bootstrap_states')->insert([
            'status' => 'BOOTSTRAP_PENDING',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bootstrap_states');
    }
};
