<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'supervisor_pin',
        'assigned_store_id',
        'phone',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'supervisor_pin',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'supervisor_pin' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function assignedStore(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'assigned_store_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::MANAGER;
    }

    public function isCashier(): bool
    {
        return $this->role === UserRole::CASHIER;
    }

    public function hasRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
