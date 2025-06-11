<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
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
        'user_type',
        'promoted_at',
        'permissions',
    ];

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
            'promoted_at' => 'datetime',
            'permissions' => 'array',
        ];
    }

    /**
     * Check if user is a superadmin
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->user_type === 'superadmin';
    }

    /**
     * Check if user is a regular user
     *
     * @return bool
     */
    public function isRegularUser(): bool
    {
        return $this->user_type === 'user';
    }

    /**
     * Promote user to superadmin
     *
     * @return void
     */
    public function promoteToSuperAdmin(): void
    {
        $this->update([
            'user_type' => 'superadmin',
            'promoted_at' => now(),
        ]);
    }

    /**
     * Demote user to regular user
     *
     * @return void
     */
    public function demoteToUser(): void
    {
        $this->update([
            'user_type' => 'user',
            'promoted_at' => null,
            'permissions' => null,
        ]);
    }

    /**
     * Check if user has specific permission
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true; // Superadmin has all permissions
        }

        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Grant permission to user
     *
     * @param string $permission
     * @return void
     */
    public function grantPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
    }

    /**
     * Revoke permission from user
     *
     * @param string $permission
     * @return void
     */
    public function revokePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_values(array_filter($permissions, fn($p) => $p !== $permission));
        $this->update(['permissions' => $permissions]);
    }

    /**
     * Scope to get only superadmin users
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('user_type', 'superadmin');
    }

    /**
     * Scope to get only regular users
     */
    public function scopeRegularUsers($query)
    {
        return $query->where('user_type', 'user');
    }
}
