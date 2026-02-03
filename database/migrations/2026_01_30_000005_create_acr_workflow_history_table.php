<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tracks all workflow movements for audit trail
     */
    public function up(): void
    {
        Schema::create('acr_workflow_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acr_id')->constrained('acrs')->onDelete('cascade');

            // Who performed the action
            $table->foreignId('performed_by_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('performed_by_employee_id')->nullable()->constrained('employees')->onDelete('set null');

            // Action details
            $table->enum('action', [
                'created',
                'updated',
                'submitted_to_io',
                'returned_to_employee',
                'io_reviewed',
                'submitted_to_co',
                'returned_to_io',
                'co_reviewed',
                'submitted_to_dossier',
                'dossier_completed',
                'pdf_generated'
            ]);

            // Status transition
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('from_holder')->nullable();
            $table->string('to_holder')->nullable();

            // Additional details
            $table->text('comments')->nullable(); // Any comments during the action
            $table->text('return_reason')->nullable(); // If action is a return
            $table->json('changes')->nullable(); // JSON of what fields changed

            // IP and user agent for audit
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['acr_id', 'created_at']);
            $table->index(['performed_by_user_id']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acr_workflow_history');
    }
};
