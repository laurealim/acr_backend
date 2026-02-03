<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ACR extends Model
{
    use HasFactory;

    protected $table = 'acrs';

    /**
     * Status constants for workflow
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED_TO_IO = 'submitted_to_io';
    const STATUS_RETURNED_TO_EMPLOYEE = 'returned_to_employee';
    const STATUS_IO_COMPLETED = 'io_completed';
    const STATUS_SUBMITTED_TO_CO = 'submitted_to_co';
    const STATUS_RETURNED_TO_IO = 'returned_to_io';
    const STATUS_CO_COMPLETED = 'co_completed';
    const STATUS_SUBMITTED_TO_DOSSIER = 'submitted_to_dossier';
    const STATUS_COMPLETED = 'completed';

    /**
     * Current holder constants
     */
    const HOLDER_EMPLOYEE = 'employee';
    const HOLDER_IO = 'io';
    const HOLDER_CO = 'co';
    const HOLDER_DOSSIER = 'dossier';
    const HOLDER_COMPLETED = 'completed';

    /**
     * Fields that can be edited by Employee
     */
    const EMPLOYEE_EDITABLE_FIELDS = [
        'reporting_year', 'name_bangla', 'name_english', 'id_number', 'batch', 'cadre', 'nid_number',
        'designation_during_period', 'workplace_during_period', 'current_designation', 'current_workplace',
        'health_height', 'health_weight', 'health_eyesight', 'health_blood_group', 'health_blood_pressure',
        'health_weakness', 'health_medical_category', 'health_checkup_date',
        'ministry_name', 'acr_period_from', 'acr_period_to', 'father_name', 'mother_name', 'date_of_birth',
        'prl_start_date', 'marital_status', 'number_of_children', 'highest_education', 'personal_email',
        'govt_service_join_date', 'gazetted_post_join_date', 'cadre_join_date',
        'position_name', 'position_workplace', 'position_join_date', 'previous_position', 'previous_workplace',
        'work_description_1', 'work_description_2', 'work_description_3', 'work_description_4', 'work_description_5',
        'partial_acr_reason',
    ];

    /**
     * Fields that can be edited by Initiating Officer
     */
    const IO_EDITABLE_FIELDS = [
        'reviewer_name', 'reviewer_designation', 'reviewer_workplace', 'reviewer_id_number', 'reviewer_email',
        'reviewer_period_from', 'reviewer_period_to', 'reviewer_previous_designation', 'reviewer_previous_workplace',
        'rating_ethics', 'rating_honesty', 'rating_discipline', 'rating_judgment', 'rating_personality',
        'rating_cooperation', 'rating_punctuality', 'rating_reliability', 'rating_responsibility',
        'rating_work_interest', 'rating_following_orders', 'rating_initiative', 'rating_client_behavior',
        'rating_professional_knowledge', 'rating_work_quality', 'rating_dedication', 'rating_work_quantity',
        'rating_decision_making', 'rating_decision_implementation', 'rating_supervision', 'rating_teamwork_leadership',
        'rating_efile_internet', 'rating_innovation', 'rating_written_expression', 'rating_verbal_expression',
        'reviewer_additional_comments', 'comment_type', 'reviewer_signature_date', 'reviewer_memo_number',
    ];

    /**
     * Fields that can be edited by Countersigning Officer
     */
    const CO_EDITABLE_FIELDS = [
        'countersigner_name', 'countersigner_designation', 'countersigner_workplace', 'countersigner_id_number',
        'countersigner_email', 'countersigner_period_from', 'countersigner_period_to',
        'countersigner_previous_designation', 'countersigner_previous_workplace',
        'countersigner_agrees', 'countersigner_agree_comment', 'countersigner_disagree_comment',
        'countersigner_same_person_reason', 'countersigner_adverse_comment',
        'countersigner_score', 'countersigner_score_in_words',
        'countersigner_signature_date', 'countersigner_memo_number',
    ];

    /**
     * Fields that can be edited by Dossier Keeper
     */
    const DOSSIER_EDITABLE_FIELDS = [
        'dossier_received_date', 'dossier_action_taken',
        'dossier_average_score', 'dossier_average_score_in_words',
    ];

    protected $fillable = [
        'user_id',
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

        // Basic Info
        'reporting_year', 'name_bangla', 'name_english', 'id_number', 'batch', 'cadre', 'nid_number',

        // Position Info
        'designation_during_period', 'workplace_during_period', 'current_designation', 'current_workplace',

        // Part 1 - Health
        'health_height', 'health_weight', 'health_eyesight', 'health_blood_group', 'health_blood_pressure',
        'health_weakness', 'health_medical_category', 'health_checkup_date',

        // Part 2 - Reviewer Info
        'reviewer_name', 'reviewer_designation', 'reviewer_workplace', 'reviewer_id_number', 'reviewer_email',
        'reviewer_period_from', 'reviewer_period_to', 'reviewer_previous_designation', 'reviewer_previous_workplace',

        // Countersigner Info
        'countersigner_name', 'countersigner_designation', 'countersigner_workplace', 'countersigner_id_number',
        'countersigner_email', 'countersigner_period_from', 'countersigner_period_to',
        'countersigner_previous_designation', 'countersigner_previous_workplace',

        'partial_acr_reason',

        // Part 3 - Personal Info
        'ministry_name', 'acr_period_from', 'acr_period_to', 'father_name', 'mother_name', 'date_of_birth',
        'prl_start_date', 'marital_status', 'number_of_children', 'highest_education', 'personal_email',

        // Service Entry
        'govt_service_join_date', 'gazetted_post_join_date', 'cadre_join_date',

        // Current Position
        'position_name', 'position_workplace', 'position_join_date', 'previous_position', 'previous_workplace',

        // Work Description
        'work_description_1', 'work_description_2', 'work_description_3', 'work_description_4', 'work_description_5',

        // Part 4 - Ratings
        'rating_ethics', 'rating_honesty', 'rating_discipline', 'rating_judgment', 'rating_personality',
        'rating_cooperation', 'rating_punctuality', 'rating_reliability', 'rating_responsibility',
        'rating_work_interest', 'rating_following_orders', 'rating_initiative', 'rating_client_behavior',
        'rating_professional_knowledge', 'rating_work_quality', 'rating_dedication', 'rating_work_quantity',
        'rating_decision_making', 'rating_decision_implementation', 'rating_supervision', 'rating_teamwork_leadership',
        'rating_efile_internet', 'rating_innovation', 'rating_written_expression', 'rating_verbal_expression',
        'total_score', 'score_in_words',

        // Part 5 - Reviewer Comments
        'reviewer_additional_comments', 'comment_type', 'reviewer_signature_date', 'reviewer_memo_number',

        // Part 6 - Countersigner Comments
        'countersigner_agrees', 'countersigner_agree_comment', 'countersigner_disagree_comment',
        'countersigner_same_person_reason', 'countersigner_adverse_comment',
        'countersigner_score', 'countersigner_score_in_words', 'countersigner_signature_date', 'countersigner_memo_number',

        // Part 7 - Dossier
        'dossier_received_date', 'dossier_action_taken', 'dossier_average_score', 'dossier_average_score_in_words',

        // Status
        'status', 'submitted_date', 'reviewed_date', 'countersigned_date',
    ];

    protected $casts = [
        // Date fields
        'health_checkup_date' => 'date',
        'reviewer_period_from' => 'date',
        'reviewer_period_to' => 'date',
        'countersigner_period_from' => 'date',
        'countersigner_period_to' => 'date',
        'acr_period_from' => 'date',
        'acr_period_to' => 'date',
        'date_of_birth' => 'date',
        'prl_start_date' => 'date',
        'govt_service_join_date' => 'date',
        'gazetted_post_join_date' => 'date',
        'cadre_join_date' => 'date',
        'position_join_date' => 'date',
        'reviewer_signature_date' => 'date',
        'countersigner_signature_date' => 'date',
        'dossier_received_date' => 'date',

        // Datetime fields
        'submitted_date' => 'datetime',
        'reviewed_date' => 'datetime',
        'countersigned_date' => 'datetime',
        'returned_at' => 'datetime',
        'pdf_generated_at' => 'datetime',
        'sent_to_io_at' => 'datetime',
        'io_completed_at' => 'datetime',
        'sent_to_co_at' => 'datetime',
        'co_completed_at' => 'datetime',
        'sent_to_dossier_at' => 'datetime',
        'completed_at' => 'datetime',

        // JSON fields
        'employee_snapshot' => 'array',
        'io_snapshot' => 'array',
        'co_snapshot' => 'array',

        // Integer fields
        'number_of_children' => 'integer',
        'rating_ethics' => 'integer',
        'rating_honesty' => 'integer',
        'rating_discipline' => 'integer',
        'rating_judgment' => 'integer',
        'rating_personality' => 'integer',
        'rating_cooperation' => 'integer',
        'rating_punctuality' => 'integer',
        'rating_reliability' => 'integer',
        'rating_responsibility' => 'integer',
        'rating_work_interest' => 'integer',
        'rating_following_orders' => 'integer',
        'rating_initiative' => 'integer',
        'rating_client_behavior' => 'integer',
        'rating_professional_knowledge' => 'integer',
        'rating_work_quality' => 'integer',
        'rating_dedication' => 'integer',
        'rating_work_quantity' => 'integer',
        'rating_decision_making' => 'integer',
        'rating_decision_implementation' => 'integer',
        'rating_supervision' => 'integer',
        'rating_teamwork_leadership' => 'integer',
        'rating_efile_internet' => 'integer',
        'rating_innovation' => 'integer',
        'rating_written_expression' => 'integer',
        'rating_verbal_expression' => 'integer',
        'total_score' => 'integer',
        'countersigner_score' => 'integer',
        'dossier_average_score' => 'integer',

        // Boolean fields
        'countersigner_agrees' => 'boolean',
        'is_returned' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * User who created this ACR (legacy)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Employee whose ACR this is
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Initiating Officer for this ACR
     */
    public function initiatingOfficer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'initiating_officer_id');
    }

    /**
     * Countersigning Officer for this ACR
     */
    public function countersigningOfficer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'countersigning_officer_id');
    }

    /**
     * Dossier Keeper who finalized this ACR
     */
    public function dossierKeeper(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'dossier_keeper_id');
    }

    /**
     * Workflow history for this ACR
     */
    public function workflowHistory(): HasMany
    {
        return $this->hasMany(AcrWorkflowHistory::class, 'acr_id');
    }

    /**
     * PDFs generated for this ACR
     */
    public function pdfs(): HasMany
    {
        return $this->hasMany(AcrPdf::class, 'acr_id');
    }

    /**
     * Latest PDF for this ACR
     */
    public function latestPdf(): HasOne
    {
        return $this->hasOne(AcrPdf::class, 'acr_id')->latestOfMany();
    }

    // ==================== WORKFLOW METHODS ====================

    /**
     * Check if ACR can be edited by employee
     */
    public function canBeEditedByEmployee(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_RETURNED_TO_EMPLOYEE])
            && $this->current_holder === self::HOLDER_EMPLOYEE;
    }

    /**
     * Check if ACR can be edited by IO
     */
    public function canBeEditedByIO(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED_TO_IO, self::STATUS_RETURNED_TO_IO])
            && $this->current_holder === self::HOLDER_IO;
    }

    /**
     * Check if ACR can be edited by CO
     */
    public function canBeEditedByCO(): bool
    {
        return $this->status === self::STATUS_SUBMITTED_TO_CO
            && $this->current_holder === self::HOLDER_CO;
    }

    /**
     * Check if ACR can be edited by Dossier Keeper
     */
    public function canBeEditedByDossier(): bool
    {
        return $this->status === self::STATUS_SUBMITTED_TO_DOSSIER
            && $this->current_holder === self::HOLDER_DOSSIER;
    }

    /**
     * Check if user can edit specific fields based on their role
     */
    public function getEditableFieldsForUser(Employee $employee): array
    {
        if ($this->employee_id === $employee->id && $this->canBeEditedByEmployee()) {
            return self::EMPLOYEE_EDITABLE_FIELDS;
        }

        if ($this->initiating_officer_id === $employee->id && $this->canBeEditedByIO()) {
            return self::IO_EDITABLE_FIELDS;
        }

        if ($this->countersigning_officer_id === $employee->id && $this->canBeEditedByCO()) {
            return self::CO_EDITABLE_FIELDS;
        }

        if ($employee->isDossierKeeper() && $this->canBeEditedByDossier()) {
            return self::DOSSIER_EDITABLE_FIELDS;
        }

        return [];
    }

    /**
     * Filter update data to only allowed fields
     */
    public function filterUpdateData(array $data, Employee $employee): array
    {
        $editableFields = $this->getEditableFieldsForUser($employee);
        return array_intersect_key($data, array_flip($editableFields));
    }

    /**
     * Check if IO can return ACR to employee
     */
    public function canIOReturnToEmployee(): bool
    {
        return $this->status === self::STATUS_SUBMITTED_TO_IO
            && $this->current_holder === self::HOLDER_IO;
    }

    /**
     * Check if CO can return ACR to IO
     */
    public function canCOReturnToIO(): bool
    {
        return $this->status === self::STATUS_SUBMITTED_TO_CO
            && $this->current_holder === self::HOLDER_CO;
    }

    /**
     * Check if ACR is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if ACR is partial (covers less than full year)
     */
    public function isPartial(): bool
    {
        return !empty($this->partial_acr_reason);
    }

    // ==================== SCORE CALCULATIONS ====================

    /**
     * Calculate total score from all 25 rating criteria
     */
    public function calculateTotalScore(): int
    {
        $ratings = [
            $this->rating_ethics,
            $this->rating_honesty,
            $this->rating_discipline,
            $this->rating_judgment,
            $this->rating_personality,
            $this->rating_cooperation,
            $this->rating_punctuality,
            $this->rating_reliability,
            $this->rating_responsibility,
            $this->rating_work_interest,
            $this->rating_following_orders,
            $this->rating_initiative,
            $this->rating_client_behavior,
            $this->rating_professional_knowledge,
            $this->rating_work_quality,
            $this->rating_dedication,
            $this->rating_work_quantity,
            $this->rating_decision_making,
            $this->rating_decision_implementation,
            $this->rating_supervision,
            $this->rating_teamwork_leadership,
            $this->rating_efile_internet,
            $this->rating_innovation,
            $this->rating_written_expression,
            $this->rating_verbal_expression,
        ];

        return array_sum(array_filter($ratings, fn($r) => $r !== null));
    }

    /**
     * Get grade label based on score
     */
    public function getGradeLabel(?int $score = null): string
    {
        $score = $score ?? $this->total_score ?? $this->calculateTotalScore();

        if ($score >= 95) return 'অসাধারণ';
        if ($score >= 90) return 'অত্যুত্তম';
        if ($score >= 80) return 'উত্তম';
        if ($score >= 70) return 'চলতিমান';
        return 'চলতি মানের নিচে';
    }

    /**
     * Get status label in Bangla
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'খসড়া',
            self::STATUS_SUBMITTED_TO_IO => 'অনুবেদনকারীর নিকট প্রেরিত',
            self::STATUS_RETURNED_TO_EMPLOYEE => 'কর্মচারীর নিকট ফেরত',
            self::STATUS_IO_COMPLETED => 'অনুবেদনকারী সম্পন্ন',
            self::STATUS_SUBMITTED_TO_CO => 'প্রতিস্বাক্ষরকারীর নিকট প্রেরিত',
            self::STATUS_RETURNED_TO_IO => 'অনুবেদনকারীর নিকট ফেরত',
            self::STATUS_CO_COMPLETED => 'প্রতিস্বাক্ষরকারী সম্পন্ন',
            self::STATUS_SUBMITTED_TO_DOSSIER => 'ডোসিয়ারে প্রেরিত',
            self::STATUS_COMPLETED => 'সম্পন্ন',
            default => $this->status,
        };
    }

    /**
     * Get current holder label in Bangla
     */
    public function getCurrentHolderLabelAttribute(): string
    {
        return match ($this->current_holder) {
            self::HOLDER_EMPLOYEE => 'কর্মচারী',
            self::HOLDER_IO => 'অনুবেদনকারী',
            self::HOLDER_CO => 'প্রতিস্বাক্ষরকারী',
            self::HOLDER_DOSSIER => 'ডোসিয়ার সংরক্ষণকারী',
            self::HOLDER_COMPLETED => 'সম্পন্ন',
            default => $this->current_holder,
        };
    }

    // ==================== SCOPES ====================

    /**
     * Scope for draft ACRs
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope for completed ACRs
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for ACRs held by a specific holder
     */
    public function scopeHeldBy($query, string $holder)
    {
        return $query->where('current_holder', $holder);
    }

    /**
     * Scope for ACRs of a specific year
     */
    public function scopeOfYear($query, string $year)
    {
        return $query->where('reporting_year', $year);
    }

    /**
     * Scope for ACRs pending with IO
     */
    public function scopePendingWithIO($query)
    {
        return $query->where('current_holder', self::HOLDER_IO)
            ->whereIn('status', [self::STATUS_SUBMITTED_TO_IO, self::STATUS_RETURNED_TO_IO]);
    }

    /**
     * Scope for ACRs pending with CO
     */
    public function scopePendingWithCO($query)
    {
        return $query->where('current_holder', self::HOLDER_CO)
            ->where('status', self::STATUS_SUBMITTED_TO_CO);
    }

    /**
     * Scope for ACRs pending with Dossier
     */
    public function scopePendingWithDossier($query)
    {
        return $query->where('current_holder', self::HOLDER_DOSSIER)
            ->where('status', self::STATUS_SUBMITTED_TO_DOSSIER);
    }

    // ==================== BOOT ====================

    /**
     * Auto-calculate and update total score before saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Calculate total score from ratings
            $model->total_score = $model->calculateTotalScore();
            $model->score_in_words = $model->getGradeLabel();

            // Calculate countersigner score label if score is set
            if ($model->countersigner_score) {
                $model->countersigner_score_in_words = $model->getGradeLabel($model->countersigner_score);
            }

            // Calculate dossier average score label if score is set
            if ($model->dossier_average_score) {
                $model->dossier_average_score_in_words = $model->getGradeLabel($model->dossier_average_score);
            }
        });
    }
}
