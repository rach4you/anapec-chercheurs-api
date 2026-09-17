<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'api_users';

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
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The roles a user may assume.
     */
    public const ROLE_ADMIN = 'admin';

    public const ROLE_USER = 'user';

    /**
     * The roles a user may assume (allowed values).
     *
     * @return list<string>
     */
    public static function allowedRoles(): array
    {
        return [self::ROLE_ADMIN, self::ROLE_USER];
    }

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
            'is_active' => 'boolean',
        ];
    }

    /**
     * Determine whether the given user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Determine whether the user is active.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * The web services associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function webServices()
    {
        return $this->belongsToMany(
            WebService::class,
            'api_user_web_services',
            'user_id',
            'web_service_id'
        )->using(UserWebService::class)->withPivot('is_enabled')->withTimestamps();
    }

    /**
     * Determine whether the user has an enabled permission for the given Web Service.
     */
    public function canConsumeWebService(string $code): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        return $this->webServices()
            ->wherePivot('is_enabled', true)
            ->where('code', $code)
            ->first() !== null;
    }
}
