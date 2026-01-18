<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Traits\HasPermissions;
use App\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles, HasPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'last_login_at',
        'profile_completed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'two_factor_confirmed_at' => 'datetime',
            'role' => UserRole::class,
            'permissions' => 'array',
            'last_login_at' => 'datetime',
            'profile_completed' => 'boolean',
        ];
    }

    /**
     * Get all pages authored by the user.
     */
    public function pages()
    {
        return $this->hasMany(Page::class, 'author_id');
    }

    /**
     * Get all news articles authored by the user.
     */
    public function news()
    {
        return $this->hasMany(News::class, 'author_id');
    }

    /**
     * Get all documents uploaded by the user.
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Mark profile as completed.
     */
    public function markProfileCompleted(): void
    {
        $this->update(['profile_completed' => true]);
    }

    /**
     * Get all users with a specific role.
     */
    public static function withRole(UserRole $role): Collection
    {
        return static::where('role', $role->value)->get();
    }

    /**
     * Get admin users.
     */
    public static function admins(): Collection
    {
        return static::whereIn('role', [UserRole::SuperAdmin->value, UserRole::Admin->value])->get();
    }
}
