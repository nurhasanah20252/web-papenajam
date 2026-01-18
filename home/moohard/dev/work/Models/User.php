<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Traits\HasPermissionsNew;
use App\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;
    use HasRoles;
    use HasPermissionsNew;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the user's role label.
     */
    public function getRoleLabelAttribute(): string
    {
        return $this->role?->label() ?? 'Unknown';
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin || $this->isSuperAdmin();
    }

    /**
     * Check if user can manage users.
     */
    public function canManageUsers(): bool
    {
        return $this->role?->canManageUsers() ?? false;
    }

    /**
     * Check if user can manage settings.
     */
    public function canManageSettings(): bool
    {
        return $this->role?->canManageSettings() ?? false;
    }

    /**
     * Set password with hashing.
     */
    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }

    /**
     * Get pages authored by this user.
     */
    public function pages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Page::class, 'author_id');
    }

    /**
     * Get news authored by this user.
     */
    public function news(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(News::class, 'author_id');
    }

    /**
     * Get documents uploaded by this user.
     */
    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    /**
     * Get activity logs for this user.
     */
    public function activityLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserActivityLog::class);
    }
}
