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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->decimal('value', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->enum('stage', [
                'new_lead',
                'contacted',
                'qualified',
                'proposal_sent',
                'negotiation',
                'won',
                'lost'
            ])->default('new_lead');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');
            $table->date('expected_close_date')->nullable();
            $table->string('lead_source')->nullable(); // website, referral, cold_call, social_media, etc.
            $table->text('notes')->nullable();
            $table->text('lost_reason')->nullable(); // Reason if deal is lost
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->timestamps();
            
            $table->index('stage');
            $table->index('priority');
            $table->index('assigned_to');
            $table->index('client_id');
            $table->index('expected_close_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
