<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Stores PDF files generated when employee submits ACR to IO
     * PDFs are organized year-wise for each employee
     */
    public function up(): void
    {
        Schema::create('acr_pdfs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acr_id')->constrained('acrs')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('reporting_year'); // Year of the ACR
            $table->string('file_name'); // Original file name
            $table->string('file_path'); // Full path to the PDF file
            $table->bigInteger('file_size'); // File size in bytes
            $table->string('mime_type')->default('application/pdf');
            $table->string('checksum')->nullable(); // SHA256 hash for integrity
            $table->boolean('is_partial')->default(false); // Partial ACR indicator
            $table->integer('partial_sequence')->default(1); // Sequence number for partial ACRs
            $table->timestamp('generated_at');
            $table->timestamps();

            // Indexes for efficient retrieval
            $table->index(['employee_id', 'reporting_year']);
            $table->index(['acr_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acr_pdfs');
    }
};
