<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
     * @return BelongsToMany
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
     * The web service domains associated with the user.
     *
     * @return BelongsToMany
     */
    public function domains()
    {
        return $this->belongsToMany(
            WebServiceDomain::class,
            'api_user_domains',
            'user_id',
            'domain_id'
        )->using(UserDomain::class)->withPivot('is_enabled')->withTimestamps();
    }

    /**
     * Determine whether the user has an enabled permission for the given Web Service.
     */
    public function canConsumeWebService(string $code): bool
    {
        $service = WebService::query()->where('code', $code)->first();

        if ($service === null) {
            return false;
        }

        return $this->hasEffectiveAccessTo($service);
    }

    /**
     * Determine whether the user has effective access to the given Web Service.
     *
     * This is the single source of truth for the effective-access rule.
     * It combines, in order:
     *   1. the user must be active
     *   2. the Web Service must be globally active
     *   3. an explicit disabled override row (`api_user_web_services.is_enabled
     *      = false`) denies access even when a domain would grant it
     *   4. access is granted when EITHER a direct enabled permission row
     *      exists OR the user is granted (via an active domain) that
     *      contains the service.
     *
     * When no domain grants or overrides exist for the user the result is
     * identical to the legacy "direct permission only" rule, so introducing
     * domains is a strict no-op until an admin actually grants one.
     */
    public function hasEffectiveAccessTo(WebService $service): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if (! $service->is_active) {
            return false;
        }

        $direct = $this->webServices()
            ->where('api_web_services.code', $service->code)
            ->first();

        // An explicit disabled override always wins, even when a domain
        // contains the service and grants it.
        if ($direct !== null && ! $direct->pivot->is_enabled) {
            return false;
        }

        if ($direct !== null && $direct->pivot->is_enabled) {
            return true;
        }

        // No direct row: fall back to the domain grant path. The service is
        // globally active (checked above) and the user is active (checked
        // above), so it is enough that an enabled, active domain contains it.
        return $this->domains()
            ->wherePivot('is_enabled', true)
            ->wherePivot('api_web_service_domains.is_active', true)
            ->whereHas('webServices', fn ($query) => $query->where('code', $service->code))
            ->exists();
    }

    /**
     * Whether the user holds an enabled direct (non-domain) permission row
     * for the given service. Exposed for payloads that need to show the
     * "direct permission" flag separately from the effective access result.
     */
    public function hasDirectPermissionFor(WebService $service): bool
    {
        return $this->webServices()
            ->wherePivot('is_enabled', true)
            ->where('code', $service->code)
            ->exists();
    }

    /**
     * The effective state of a Web Service for this user, as shown in the
     * user self-service and admin payloads. `grant_source` reports *how*
     * access is obtained: `direct`, `domain`, `override_disabled`, or
     * `none` when there is no access.
     */
    public function effectiveAccessState(WebService $service): array
    {
        $directRow = $this->webServices()
            ->where('api_web_services.code', $service->code)
            ->first();

        $hasDomainGrant = $this->domains()
            ->wherePivot('is_enabled', true)
            ->wherePivot('api_web_service_domains.is_active', true)
            ->whereHas('webServices', fn ($query) => $query->where('code', $service->code))
            ->exists();

        $overrideDisabled = $directRow !== null && ! $directRow->pivot->is_enabled;

        $directEnabled = $this->hasDirectPermissionFor($service);

        $active = $this->isActive() && $service->is_active;

        $effective = $active && ($directEnabled || ($hasDomainGrant && ! $overrideDisabled));

        if (! $active) {
            $source = 'none';
        } elseif ($overrideDisabled) {
            $source = 'override_disabled';
        } elseif ($directEnabled) {
            $source = 'direct';
        } elseif ($hasDomainGrant) {
            $source = 'domain';
        } else {
            $source = 'none';
        }

        return [
            'code' => $service->code,
            'name' => $service->name,
            'description' => $service->description,
            'global_is_active' => $service->is_active,
            'is_enabled' => $directRow !== null ? (bool) $directRow->pivot->is_enabled : false,
            'domain_granted' => $hasDomainGrant,
            'override_disabled' => $overrideDisabled,
            'grant_source' => $source,
            'effective_access' => $effective,
        ];
    }
}
