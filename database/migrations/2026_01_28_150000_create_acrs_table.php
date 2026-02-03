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
        Schema::create('acrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Employee Information
            $table->string('employee_id')->unique();
            $table->string('employee_name');
            $table->string('designation');
            $table->string('department');
            $table->string('reporting_to');
            $table->date('appraisal_period_from');
            $table->date('appraisal_period_to');

            // Performance Indicators (1-5 scale)
            $table->integer('job_knowledge')->default(0);
            $table->integer('job_efficiency')->default(0);
            $table->integer('reliability')->default(0);
            $table->integer('attendance')->default(0);
            $table->integer('quality_of_work')->default(0);
            $table->integer('initiative')->default(0);
            $table->integer('teamwork')->default(0);
            $table->integer('communication')->default(0);

            // Assessment Section
            $table->integer('overall_rating')->default(0);
            $table->longText('employee_comments')->nullable();
            $table->longText('appraiser_comments')->nullable();
            $table->longText('final_comments')->nullable();

            // Recommendations
            $table->longText('recommendations')->nullable();
            $table->boolean('promotion_eligible')->default(false);
            $table->string('training_required')->nullable();

            // Status Management
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'approved'])->default('draft');
            $table->timestamp('submitted_date')->nullable();
            $table->timestamp('reviewed_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acrs');
    }
};
