<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates employee profiles linked to users with grade system (1-20)
     * Grade 1-9: 1st Class Officers (can be IO/CO)
     * Grade 10-13: 2nd Class Officers
     * Grade 14-16: 3rd Class Staff
     * Grade 17-20: 4th Class Staff
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('office_id')->constrained()->onDelete('cascade');

            // Personal Information
            $table->string('employee_id')->unique(); // Government ID number
            $table->string('name_bangla');
            $table->string('name_english');
            $table->string('nid_number')->nullable();
            $table->date('date_of_birth');
            $table->string('father_name');
            $table->string('mother_name');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed']);
            $table->integer('number_of_children')->default(0);
            $table->string('blood_group')->nullable();
            $table->string('personal_email')->nullable();
            $table->string('personal_phone')->nullable();
            $table->text('permanent_address')->nullable();
            $table->text('present_address')->nullable();

            // Employment Information
            $table->tinyInteger('grade'); // 1-20 grade system
            $table->enum('employee_class', ['1st_class', '2nd_class', '3rd_class', '4th_class']);
            $table->string('designation');
            $table->string('cadre')->nullable();
            $table->string('batch')->nullable();
            $table->date('govt_service_join_date');
            $table->date('gazetted_post_join_date')->nullable();
            $table->date('cadre_join_date')->nullable();
            $table->date('current_position_join_date');
            $table->date('prl_date')->nullable(); // Pension Retirement Leave date
            $table->string('highest_education');
            $table->string('photo')->nullable(); // Profile photo path

            // Dossier Role - Can be assigned by Office Admin
            $table->boolean('is_dossier_keeper')->default(false);

            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('suspended_at')->nullable();
            $table->text('suspension_reason')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['office_id', 'grade']);
            $table->index(['grade', 'is_active']);
            $table->index('is_dossier_keeper');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
