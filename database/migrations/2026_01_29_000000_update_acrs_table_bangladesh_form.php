<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Updates ACR table to match Bangladesh Government Form No. 290-Gha (2020 Revised)
     */
    public function up(): void
    {
        // Drop the old table and create new one with updated structure
        Schema::dropIfExists('acrs');

        Schema::create('acrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Basic Info - প্রাথমিক তথ্য
            $table->string('reporting_year'); // বৎসর/সময়
            $table->string('name_bangla'); // নাম (বাংলা)
            $table->string('name_english'); // নাম (ইংরেজি)
            $table->string('id_number')->nullable(); // আইডি নম্বর
            $table->string('batch')->nullable(); // ব্যাচ
            $table->string('cadre')->nullable(); // ক্যাডার
            $table->string('nid_number')->nullable(); // এনআইডি নম্বর

            // Position Info - পদবি সংক্রান্ত
            $table->string('designation_during_period'); // অনুবেদনে বিবেচ্য সময়ের পদবি
            $table->string('workplace_during_period'); // কর্মস্থল
            $table->string('current_designation')->nullable(); // বর্তমান পদবি
            $table->string('current_workplace')->nullable(); // বর্তমান কর্মস্থল

            // Part 1 - ১ম অংশ: স্বাস্থ্য পরীক্ষা প্রতিবেদন
            $table->string('health_height')->nullable(); // উচ্চতা (মিটার)
            $table->string('health_weight')->nullable(); // ওজন (কেজি)
            $table->string('health_eyesight')->nullable(); // দৃষ্টিশক্তি
            $table->string('health_blood_group')->nullable(); // রক্তের গ্রুপ
            $table->string('health_blood_pressure')->nullable(); // রক্তচাপ
            $table->text('health_weakness')->nullable(); // স্বাস্থ্যগত দুর্বলতা
            $table->string('health_medical_category')->nullable(); // চিকিৎসাগত শ্রেণিবিভাগ
            $table->date('health_checkup_date')->nullable(); // তারিখ

            // Part 2 - ২য় অংশ: অনুবেদনকারী তথ্য
            $table->string('reviewer_name'); // অনুবেদনকারীর নাম
            $table->string('reviewer_designation'); // পদবি
            $table->string('reviewer_workplace'); // কর্মস্থল
            $table->string('reviewer_id_number')->nullable(); // আইডি নম্বর
            $table->string('reviewer_email')->nullable(); // ই-মেইল
            $table->date('reviewer_period_from'); // প্রকৃত কর্মকাল হতে
            $table->date('reviewer_period_to'); // পর্যন্ত
            $table->string('reviewer_previous_designation')->nullable(); // প্রাক্তন পদবি
            $table->string('reviewer_previous_workplace')->nullable(); // প্রাক্তন কর্মস্থল

            // প্রতিস্বাক্ষরকারী তথ্য
            $table->string('countersigner_name')->nullable();
            $table->string('countersigner_designation')->nullable();
            $table->string('countersigner_workplace')->nullable();
            $table->string('countersigner_id_number')->nullable();
            $table->string('countersigner_email')->nullable();
            $table->date('countersigner_period_from')->nullable();
            $table->date('countersigner_period_to')->nullable();
            $table->string('countersigner_previous_designation')->nullable();
            $table->string('countersigner_previous_workplace')->nullable();

            $table->text('partial_acr_reason')->nullable(); // আংশিক গোপনীয় অনুবেদনের কারণ

            // Part 3 - ৩য় অংশ: ব্যক্তিগত তথ্য
            $table->string('ministry_name'); // মন্ত্রণালয়/বিভাগ/অফিসের নাম
            $table->date('acr_period_from'); // সময়কাল হতে
            $table->date('acr_period_to'); // পর্যন্ত
            $table->string('father_name'); // পিতার নাম
            $table->string('mother_name'); // মাতার নাম
            $table->date('date_of_birth'); // জন্ম তারিখ
            $table->date('prl_start_date')->nullable(); // পিআরএল শুরুর তারিখ
            $table->string('marital_status'); // বৈবাহিক অবস্থা
            $table->integer('number_of_children')->default(0)->nullable(); // সন্তান সংখ্যা
            $table->string('highest_education'); // সর্বোচ্চ শিক্ষাগত যোগ্যতা
            $table->string('personal_email')->nullable(); // ই-মেইল

            // Service Entry - চাকরিতে প্রবেশ
            $table->date('govt_service_join_date')->nullable();
            $table->date('gazetted_post_join_date')->nullable();
            $table->date('cadre_join_date')->nullable();

            // Current Position - বর্তমান পদ
            $table->string('position_name'); // পদের নাম
            $table->string('position_workplace'); // কর্মস্থল
            $table->date('position_join_date'); // যোগদানের তারিখ
            $table->string('previous_position')->nullable();
            $table->string('previous_workplace')->nullable();

            // Work Description - কাজের সংক্ষিপ্ত বিবরণ
            $table->text('work_description_1')->nullable();
            $table->text('work_description_2')->nullable();
            $table->text('work_description_3')->nullable();
            $table->text('work_description_4')->nullable();
            $table->text('work_description_5')->nullable();

            // Part 4 - ৪র্থ অংশ: মূল্যায়ন (25 criteria, 1-4 scale, total 100)
            // ব্যক্তিগত বৈশিষ্ট্য (Personal Traits)
            $table->integer('rating_ethics')->default(3); // নৈতিকতা
            $table->integer('rating_honesty')->default(3); // সততা
            $table->integer('rating_discipline')->default(3); // শৃঙ্খলাবোধ
            $table->integer('rating_judgment')->default(3); // বিচার ও মাত্রাজ্ঞান
            $table->integer('rating_personality')->default(3); // ব্যক্তিত্ব
            $table->integer('rating_cooperation')->default(3); // সহযোগিতার মনোভাব
            $table->integer('rating_punctuality')->default(3); // সময়ানুবর্তিতা
            $table->integer('rating_reliability')->default(3); // নির্ভরযোগ্যতা
            $table->integer('rating_responsibility')->default(3); // দায়িত্ববোধ
            $table->integer('rating_work_interest')->default(3); // কাজে আগ্রহ ও মনোযোগ
            $table->integer('rating_following_orders')->default(3); // ঊর্ধ্বতন কর্তৃপক্ষের নির্দেশনা পালনে তৎপরতা
            $table->integer('rating_initiative')->default(3); // উদ্যম ও উদ্যোগ
            $table->integer('rating_client_behavior')->default(3); // সেবাগ্রহীতার সঙ্গে ব্যবহার

            // কার্যসম্পাদন (Work Performance)
            $table->integer('rating_professional_knowledge')->default(3); // পেশাগত জ্ঞান
            $table->integer('rating_work_quality')->default(3); // কাজের মান
            $table->integer('rating_dedication')->default(3); // কর্তব্যনিষ্ঠা
            $table->integer('rating_work_quantity')->default(3); // সম্পাদিত কাজের পরিমাণ
            $table->integer('rating_decision_making')->default(3); // সিদ্ধান্ত গ্রহণে দক্ষতা
            $table->integer('rating_decision_implementation')->default(3); // সিদ্ধান্ত বাস্তবায়নে সামর্থ্য
            $table->integer('rating_supervision')->default(3); // অধীনস্থদের তদারকি ও পরিচালনায় সামর্থ্য
            $table->integer('rating_teamwork_leadership')->default(3); // দলগত কাজে সহযোগিতা ও নেতৃত্ব
            $table->integer('rating_efile_internet')->default(3); // ই-নথি ও ইন্টারনেট ব্যবহারে আগ্রহ ও দক্ষতা
            $table->integer('rating_innovation')->default(3); // উদ্ভাবনী কাজে আগ্রহ ও সক্ষমতা
            $table->integer('rating_written_expression')->default(3); // প্রকাশ ক্ষমতা (লিখন)
            $table->integer('rating_verbal_expression')->default(3); // প্রকাশ ক্ষমতা (বাচনিক)

            $table->integer('total_score')->nullable(); // মোট প্রাপ্ত নম্বর
            $table->string('score_in_words')->nullable(); // কথায়

            // Part 5 - ৫ম অংশ: অনুবেদনকারীর মন্তব্য
            $table->text('reviewer_additional_comments')->nullable();
            $table->enum('comment_type', ['praise', 'adverse'])->nullable();
            $table->date('reviewer_signature_date')->nullable();
            $table->string('reviewer_memo_number')->nullable();

            // Part 6 - ৬ষ্ঠ অংশ: প্রতিস্বাক্ষরকারীর মন্তব্য
            $table->boolean('countersigner_agrees')->default(true);
            $table->text('countersigner_agree_comment')->nullable();
            $table->text('countersigner_disagree_comment')->nullable();
            $table->text('countersigner_same_person_reason')->nullable();
            $table->text('countersigner_adverse_comment')->nullable();
            $table->integer('countersigner_score')->nullable();
            $table->string('countersigner_score_in_words')->nullable();
            $table->date('countersigner_signature_date')->nullable();
            $table->string('countersigner_memo_number')->nullable();

            // Part 7 - ৭ম অংশ: ডোসিয়ার সংরক্ষণকারী
            $table->date('dossier_received_date')->nullable();
            $table->text('dossier_action_taken')->nullable();
            $table->integer('dossier_average_score')->nullable();
            $table->string('dossier_average_score_in_words')->nullable();

            // Status Management
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'countersigned', 'approved'])->default('draft');
            $table->timestamp('submitted_date')->nullable();
            $table->timestamp('reviewed_date')->nullable();
            $table->timestamp('countersigned_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acrs');

        // Recreate old table structure
        Schema::create('acrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('employee_id')->unique();
            $table->string('employee_name');
            $table->string('designation');
            $table->string('department');
            $table->string('reporting_to');
            $table->date('appraisal_period_from');
            $table->date('appraisal_period_to');
            $table->integer('job_knowledge')->default(0);
            $table->integer('job_efficiency')->default(0);
            $table->integer('reliability')->default(0);
            $table->integer('attendance')->default(0);
            $table->integer('quality_of_work')->default(0);
            $table->integer('initiative')->default(0);
            $table->integer('teamwork')->default(0);
            $table->integer('communication')->default(0);
            $table->integer('overall_rating')->default(0);
            $table->longText('employee_comments')->nullable();
            $table->longText('appraiser_comments')->nullable();
            $table->longText('final_comments')->nullable();
            $table->longText('recommendations')->nullable();
            $table->boolean('promotion_eligible')->default(false);
            $table->string('training_required')->nullable();
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'approved'])->default('draft');
            $table->timestamp('submitted_date')->nullable();
            $table->timestamp('reviewed_date')->nullable();
            $table->timestamps();
        });
    }
};