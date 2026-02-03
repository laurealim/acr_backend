<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeAdmin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'office_id',
        'can_assign_dossier',
        'can_manage_employees',
        'is_active',
    ];

    protected $casts = [
        'can_assign_dossier' => 'boolean',
        'can_manage_employees' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user associated with this admin role
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the office this admin manages
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Check if admin can assign dossier keeper role
     */
    public function canAssignDossier(): bool
    {
        return $this->can_assign_dossier && $this->is_active;
    }

    /**
     * Check if admin can manage employees
     */
    public function canManageEmployees(): bool
    {
        return $this->can_manage_employees && $this->is_active;
    }

    /**
     * Scope for active admins
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
