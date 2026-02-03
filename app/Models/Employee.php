<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'office_id',
        'employee_id',
        'name_bangla',
        'name_english',
        'nid_number',
        'date_of_birth',
        'father_name',
        'mother_name',
        'gender',
        'marital_status',
        'number_of_children',
        'blood_group',
        'personal_email',
        'personal_phone',
        'permanent_address',
        'present_address',
        'grade',
        'employee_class',
        'designation',
        'cadre',
        'batch',
        'govt_service_join_date',
        'gazetted_post_join_date',
        'cadre_join_date',
        'current_position_join_date',
        'prl_date',
        'highest_education',
        'photo',
        'is_dossier_keeper',
        'is_active',
        'suspended_at',
        'suspension_reason',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'govt_service_join_date' => 'date',
        'gazetted_post_join_date' => 'date',
        'cadre_join_date' => 'date',
        'current_position_join_date' => 'date',
        'prl_date' => 'date',
        'is_dossier_keeper' => 'boolean',
        'is_active' => 'boolean',
        'suspended_at' => 'datetime',
        'number_of_children' => 'integer',
        'grade' => 'integer',
    ];

    /**
     * Employee class constants based on grade
     */
    const CLASS_1ST = '1st_class';    // Grade 1-9
    const CLASS_2ND = '2nd_class';    // Grade 10-13
    const CLASS_3RD = '3rd_class';    // Grade 14-16
    const CLASS_4TH = '4th_class';    // Grade 17-20

    /**
     * Get the user account for this employee
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // public function users() {
    //     $userClass = config('tyro.models.user', config('auth.providers.users.model', 'App\\Models\\User'));

    //     return $this->belongsToMany($userClass, config('tyro.tables.pivot', 'user_roles'));
    // }

    /**
     * Get the office this employee belongs to
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get ACRs where this employee is the subject
     */
    public function acrs(): HasMany
    {
        return $this->hasMany(ACR::class, 'employee_id');
    }

    /**
     * Get ACRs where this employee is the Initiating Officer
     */
    public function acrsAsInitiatingOfficer(): HasMany
    {
        return $this->hasMany(ACR::class, 'initiating_officer_id');
    }

    /**
     * Get ACRs where this employee is the Countersigning Officer
     */
    public function acrsAsCountersigningOfficer(): HasMany
    {
        return $this->hasMany(ACR::class, 'countersigning_officer_id');
    }

    /**
     * Get ACRs where this employee is the Dossier Keeper
     */
    public function acrsAsDossierKeeper(): HasMany
    {
        return $this->hasMany(ACR::class, 'dossier_keeper_id');
    }

    /**
     * Get PDFs for this employee's ACRs
     */
    public function acrPdfs(): HasMany
    {
        return $this->hasMany(AcrPdf::class);
    }

    /**
     * Check if employee is a 1st class officer (can be IO/CO)
     */
    public function isFirstClassOfficer(): bool
    {
        return $this->grade >= 1 && $this->grade <= 9;
    }

    /**
     * Check if employee can be an Initiating Officer
     */
    public function canBeInitiatingOfficer(): bool
    {
        return $this->isFirstClassOfficer() && $this->is_active;
    }

    /**
     * Check if employee can be a Countersigning Officer
     */
    public function canBeCountersigningOfficer(): bool
    {
        return $this->isFirstClassOfficer() && $this->is_active;
    }

    /**
     * Check if employee is a Dossier Keeper
     */
    public function isDossierKeeper(): bool
    {
        return $this->is_dossier_keeper && $this->is_active;
    }

    /**
     * Get pending ACRs for this employee as IO
     */
    public function pendingAcrsAsIO(): HasMany
    {
        return $this->acrsAsInitiatingOfficer()
            ->where('current_holder', 'io')
            ->whereIn('status', ['submitted_to_io', 'returned_to_io']);
    }

    /**
     * Get pending ACRs for this employee as CO
     */
    public function pendingAcrsAsCO(): HasMany
    {
        return $this->acrsAsCountersigningOfficer()
            ->where('current_holder', 'co')
            ->where('status', 'submitted_to_co');
    }

    /**
     * Get pending ACRs for Dossier Keeper
     */
    public function pendingAcrsAsDossier(): HasMany
    {
        return $this->acrsAsDossierKeeper()
            ->where('current_holder', 'dossier')
            ->where('status', 'submitted_to_dossier');
    }

    /**
     * Calculate employee class based on grade
     */
    public static function calculateClass(int $grade): string
    {
        if ($grade >= 1 && $grade <= 9) return self::CLASS_1ST;
        if ($grade >= 10 && $grade <= 13) return self::CLASS_2ND;
        if ($grade >= 14 && $grade <= 16) return self::CLASS_3RD;
        return self::CLASS_4TH;
    }

    /**
     * Get snapshot data for ACR historical record
     */
    public function getSnapshotData(): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'name_bangla' => $this->name_bangla,
            'name_english' => $this->name_english,
            'designation' => $this->designation,
            'grade' => $this->grade,
            'office_id' => $this->office_id,
            'office_name' => $this->office?->name_bangla,
            'cadre' => $this->cadre,
            'batch' => $this->batch,
            'snapshot_at' => now()->toISOString(),
        ];
    }

    /**
     * Auto-set employee class based on grade when saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($employee) {
            if ($employee->grade) {
                $employee->employee_class = self::calculateClass($employee->grade);
            }
        });
    }

    /**
     * Scope for active employees
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for first class officers
     */
    public function scopeFirstClass($query)
    {
        return $query->where('grade', '<=', 9);
    }

    /**
     * Scope for dossier keepers
     */
    public function scopeDossierKeepers($query)
    {
        return $query->where('is_dossier_keeper', true);
    }

    /**
     * Scope for employees in a specific office
     */
    public function scopeInOffice($query, int $officeId)
    {
        return $query->where('office_id', $officeId);
    }
}
