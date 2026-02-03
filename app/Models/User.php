<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use HasinHayder\Tyro\Concerns\HasTyroRoles;
use HasinHayder\TyroLogin\Traits\HasTwoFactorAuth;



class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasTyroRoles, HasTwoFactorAuth;


    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Get the employee profile for this user
     */
    public function employees()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Get offices where this user is an admin
     */
    public function adminOffices()
    {
        return $this->hasMany(OfficeAdmin::class);
    }

    /**
     * Check if user has an employee profile
     */
    public function hasEmployeeProfile(): bool
    {
        return $this->employee !== null;
    }

    /**
     * Check if user is an office admin for any office
     */
    public function isOfficeAdmin(): bool
    {
        return $this->adminOffices()->where('is_active', true)->exists();
    }

    /**
     * Check if user is an office admin for a specific office
     */
    public function isOfficeAdminFor(int $officeId): bool
    {
        return $this->adminOffices()
            ->where('office_id', $officeId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
