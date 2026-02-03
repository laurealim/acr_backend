<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds workflow columns for IO/CO relationships and status tracking
     */
    public function up(): void
    {
        Schema::table('acrs', function (Blueprint $table) {
            // Employee relationship - the employee whose ACR this is
            $table->foreignId('employee_id')->nullable()->after('user_id')->constrained('employees')->onDelete('cascade');

            // Initiating Officer (IO) - must be 1st class officer (grade 1-9)
            $table->foreignId('initiating_officer_id')->nullable()->after('employee_id')->constrained('employees')->onDelete('set null');

            // Countersigning Officer (CO) - must be 1st class officer (grade 1-9)
            $table->foreignId('countersigning_officer_id')->nullable()->after('initiating_officer_id')->constrained('employees')->onDelete('set null');

            // Dossier Keeper - who processed the final step
            $table->foreignId('dossier_keeper_id')->nullable()->after('countersigning_officer_id')->constrained('employees')->onDelete('set null');

            // Current holder of the ACR in the workflow
            $table->enum('current_holder', ['employee', 'io', 'co', 'dossier', 'completed'])->default('employee')->after('dossier_keeper_id');

            // Track if ACR was returned (reverse flow)
            $table->boolean('is_returned')->default(false)->after('current_holder');
            $table->enum('returned_from', ['io', 'co'])->nullable()->after('is_returned');
            $table->text('return_reason')->nullable()->after('returned_from');
            $table->timestamp('returned_at')->nullable()->after('return_reason');

            // PDF tracking
            $table->string('pdf_path')->nullable()->after('returned_at'); // Path to generated PDF
            $table->timestamp('pdf_generated_at')->nullable()->after('pdf_path');

            // Snapshot of employee data at submission time (for historical accuracy)
            $table->json('employee_snapshot')->nullable()->after('pdf_generated_at');
            $table->json('io_snapshot')->nullable()->after('employee_snapshot');
            $table->json('co_snapshot')->nullable()->after('io_snapshot');

            // Status update - modify the enum to include more states
            // We need to add new status values for the workflow

            // Timestamps for each workflow step
            $table->timestamp('sent_to_io_at')->nullable()->after('co_snapshot');
            $table->timestamp('io_completed_at')->nullable()->after('sent_to_io_at');
            $table->timestamp('sent_to_co_at')->nullable()->after('io_completed_at');
            $table->timestamp('co_completed_at')->nullable()->after('sent_to_co_at');
            $table->timestamp('sent_to_dossier_at')->nullable()->after('co_completed_at');
            $table->timestamp('completed_at')->nullable()->after('sent_to_dossier_at');

            // Indexes for faster queries
            $table->index(['employee_id', 'reporting_year']);
            $table->index(['initiating_officer_id', 'current_holder']);
            $table->index(['countersigning_officer_id', 'current_holder']);
            $table->index(['current_holder', 'status']);
        });

        // Update status enum to include more workflow states
        // Using raw SQL because Laravel doesn't support modifying enums well
        DB::statement("ALTER TABLE acrs MODIFY COLUMN status ENUM('draft', 'submitted_to_io', 'returned_to_employee', 'io_completed', 'submitted_to_co', 'returned_to_io', 'co_completed', 'submitted_to_dossier', 'completed') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acrs', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['employee_id', 'reporting_year']);
            $table->dropIndex(['initiating_officer_id', 'current_holder']);
            $table->dropIndex(['countersigning_officer_id', 'current_holder']);
            $table->dropIndex(['current_holder', 'status']);

            // Drop foreign keys and columns
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['initiating_officer_id']);
            $table->dropForeign(['countersigning_officer_id']);
            $table->dropForeign(['dossier_keeper_id']);

            $table->dropColumn([
                'employee_id',
                'initiating_officer_id',
                'countersigning_officer_id',
                'dossier_keeper_id',
                'current_holder',
                'is_returned',
                'returned_from',
                'return_reason',
                'returned_at',
                'pdf_path',
                'pdf_generated_at',
                'employee_snapshot',
                'io_snapshot',
                'co_snapshot',
                'sent_to_io_at',
                'io_completed_at',
                'sent_to_co_at',
                'co_completed_at',
                'sent_to_dossier_at',
                'completed_at',
            ]);
        });

        // Revert status enum
        DB::statement("ALTER TABLE acrs MODIFY COLUMN status ENUM('draft', 'submitted', 'reviewed', 'countersigned', 'approved') DEFAULT 'draft'");
    }
};
