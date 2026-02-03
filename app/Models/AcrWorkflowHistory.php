<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcrWorkflowHistory extends Model
{
    use HasFactory;

    protected $table = 'acr_workflow_history';

    protected $fillable = [
        'acr_id',
        'performed_by_user_id',
        'performed_by_employee_id',
        'action',
        'from_status',
        'to_status',
        'from_holder',
        'to_holder',
        'comments',
        'return_reason',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /**
     * Action constants
     */
    const ACTION_CREATED = 'created';
    const ACTION_UPDATED = 'updated';
    const ACTION_SUBMITTED_TO_IO = 'submitted_to_io';
    const ACTION_RETURNED_TO_EMPLOYEE = 'returned_to_employee';
    const ACTION_IO_REVIEWED = 'io_reviewed';
    const ACTION_SUBMITTED_TO_CO = 'submitted_to_co';
    const ACTION_RETURNED_TO_IO = 'returned_to_io';
    const ACTION_CO_REVIEWED = 'co_reviewed';
    const ACTION_SUBMITTED_TO_DOSSIER = 'submitted_to_dossier';
    const ACTION_DOSSIER_COMPLETED = 'dossier_completed';
    const ACTION_PDF_GENERATED = 'pdf_generated';

    /**
     * Get the ACR this history entry belongs to
     */
    public function acr(): BelongsTo
    {
        return $this->belongsTo(ACR::class);
    }

    /**
     * Get the user who performed the action
     */
    public function performedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }

    /**
     * Get the employee who performed the action
     */
    public function performedByEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'performed_by_employee_id');
    }

    /**
     * Get human-readable action label
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => 'ACR Created',
            self::ACTION_UPDATED => 'ACR Updated',
            self::ACTION_SUBMITTED_TO_IO => 'Submitted to Initiating Officer',
            self::ACTION_RETURNED_TO_EMPLOYEE => 'Returned to Employee',
            self::ACTION_IO_REVIEWED => 'Reviewed by Initiating Officer',
            self::ACTION_SUBMITTED_TO_CO => 'Submitted to Countersigning Officer',
            self::ACTION_RETURNED_TO_IO => 'Returned to Initiating Officer',
            self::ACTION_CO_REVIEWED => 'Reviewed by Countersigning Officer',
            self::ACTION_SUBMITTED_TO_DOSSIER => 'Submitted to Dossier Keeper',
            self::ACTION_DOSSIER_COMPLETED => 'Completed by Dossier Keeper',
            self::ACTION_PDF_GENERATED => 'PDF Generated',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Create a history entry for an action
     */
    public static function createEntry(
        ACR $acr,
        string $action,
        string $fromStatus = null,
        string $toStatus = null,
        string $fromHolder = null,
        string $toHolder = null,
        ?string $comments = null,
        ?string $returnReason = null,
        ?array $changes = null
    ): self {
        return self::create([
            'acr_id' => $acr->id,
            'performed_by_user_id' => auth()->id(),
            'performed_by_employee_id' => auth()->user()?->employee?->id,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'from_holder' => $fromHolder,
            'to_holder' => $toHolder,
            'comments' => $comments,
            'return_reason' => $returnReason,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Scope for a specific ACR
     */
    public function scopeForAcr($query, int $acrId)
    {
        return $query->where('acr_id', $acrId);
    }

    /**
     * Scope for a specific action type
     */
    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
