<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_bangla',
        'name_english',
        'code',
        'type',
        'parent_id',
        'address',
        'phone',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Office types for hierarchy
     */
    const TYPE_MINISTRY = 'ministry';
    const TYPE_DIVISION = 'division';
    const TYPE_DEPARTMENT = 'department';
    const TYPE_OFFICE = 'office';

    /**
     * Get the parent office
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'parent_id');
    }

    /**
     * Get child offices
     */
    public function children(): HasMany
    {
        return $this->hasMany(Office::class, 'parent_id');
    }

    /**
     * Get all employees in this office
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get office admins for this office
     */
    public function admins(): HasMany
    {
        return $this->hasMany(OfficeAdmin::class);
    }

    /**
     * Get all descendant offices (recursive)
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestor offices (recursive)
     */
    public function ancestors()
    {
        return $this->parent()->with('ancestors');
    }

    /**
     * Get the full hierarchy path of this office
     */
    public function getHierarchyPathAttribute(): array
    {
        $path = [];
        $current = $this;

        while ($current) {
            array_unshift($path, [
                'id' => $current->id,
                'name' => $current->name_bangla,
                'type' => $current->type,
            ]);
            $current = $current->parent;
        }

        return $path;
    }

    /**
     * Get the ministry for this office
     */
    public function getMinistryAttribute()
    {
        $current = $this;
        while ($current && $current->type !== self::TYPE_MINISTRY) {
            $current = $current->parent;
        }
        return $current;
    }

    /**
     * Get first class officers (grade 1-9) in this office
     */
    public function firstClassOfficers(): HasMany
    {
        return $this->hasMany(Employee::class)
            ->where('grade', '<=', 9)
            ->where('is_active', true);
    }

    /**
     * Get dossier keepers in this office
     */
    public function dossierKeepers(): HasMany
    {
        return $this->hasMany(Employee::class)
            ->where('is_dossier_keeper', true)
            ->where('is_active', true);
    }

    /**
     * Scope for active offices
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for offices by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
