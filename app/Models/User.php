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
        'access_scope',
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
     * The user has access to ALL ACTIVE Web Services in ALL domains
     * (including domains created later). No per-service rows required.
     */
    public const ACCESS_SCOPE_ALL = 'all';

    /**
     * The user has access only to the Web Services explicitly enabled in
     * `api_user_web_services`.
     */
    public const ACCESS_SCOPE_SELECTED = 'selected';

    /**
     * The allowed values for {@see accessScope}.
     *
     * @return list<string>
     */
    public static function accessScopes(): array
    {
        return [self::ACCESS_SCOPE_ALL, self::ACCESS_SCOPE_SELECTED];
    }

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
     * Determine whether the user has global ("all domains") Web Service
     * access. Such users reach every ACTIVE Web Service dynamically,
     * including services in domains created later, without per-service
     * permission rows.
     */
    public function hasGlobalAccessScope(): bool
    {
        return $this->access_scope === self::ACCESS_SCOPE_ALL;
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
     * The Web Service codes the user is *configured* for. This is the
     * "configured access" indicator shown by the admin list: it survives
     * service inactivation and is effective again when the service is
     * reactivated, so only the *service* is_active flag is ignored here.
     *
     * Semantics:
     *   - scope "all": every ACTIVE Web Service in the catalog (dynamic: a
     *     newly created active service is included automatically, with no
     *     per-service permission row)
     *   - scope "selected": the explicitly enabled rows from
     *     `api_user_web_services` only. Selecting a domain groups the UI but
     *     does NOT expand into its services on its own.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function configuredServiceCodes(): \Illuminate\Support\Collection
    {
        if ($this->hasGlobalAccessScope()) {
            return WebService::query()
                ->where('is_active', true)
                ->pluck('code')
                ->values();
        }

        return $this->webServices()
            ->wherePivot('is_enabled', true)
            ->pluck('api_web_services.code')
            ->values();
    }

    /**
     * The Web Service codes the user can actually consume *right now*.
     *
     * A configured service is effective only when the user is active, the
     * service is globally active, and an explicit disabled override row does
     * not deny it. Disabling a service globally does not delete the
     * configured permission: it becomes effective again on reactivation.
     *
     * This mirrors {@see hasEffectiveAccessTo()} in bulk so the admin list
     * page can display a single "effective access" indicator without one
     * N+1 call per catalog service.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function effectiveServiceCodes(): \Illuminate\Support\Collection
    {
        if (! $this->isActive()) {
            return collect();
        }

        $configured = $this->configuredServiceCodes();

        if ($configured->isEmpty()) {
            return collect();
        }

        $overrides = $this->webServices()
            ->wherePivot('is_enabled', false)
            ->pluck('api_web_services.code')
            ->values()
            ->all();

        return WebService::query()
            ->where(function ($query) use ($configured) {
                $query->whereIn('code', $configured->all());

                if ($this->hasGlobalAccessScope()) {
                    $query->orWhere('is_active', true);
                }
            })
            ->whereNotIn('code', $overrides)
            ->where('is_active', true)
            ->pluck('code')
            ->values();
    }

    /**
     * The user's most recent API session activity, or null when the user has
     * never called the API. Exposed for the admin list "Dernière activité".
     */
    public function lastActivityAt(): ?\Illuminate\Support\Carbon
    {
        $token = $this->tokens()->orderByDesc('last_used_at')->first();

        return $token?->last_used_at;
    }

    /**
     * The total number of ACTIVE Web Services currently in the catalog.
     * Used as the denominator of the "effective access" indicator on the
     * admin user list.
     */
    public static function activeServiceCount(): int
    {
        return WebService::query()->where('is_active', true)->count();
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
     *   3. the user must hold either the global "all" scope or an enabled
     *      direct permission row.
     *
     * Selected domains act only as a UI grouping/scope for the individual
     * Web Service checkboxes; they never expand into their whole service
     * list on their own.
     */
    public function hasEffectiveAccessTo(WebService $service): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if (! $service->is_active) {
            return false;
        }

        // An explicit disabled override always wins — even over global scope.
        $overrideRow = $this->webServices()
            ->where('api_web_services.code', $service->code)
            ->wherePivot('is_enabled', false)
            ->first();

        if ($overrideRow) {
            return false;
        }

        if ($this->hasGlobalAccessScope()) {
            return true;
        }

        return $this->webServices()
            ->wherePivot('is_enabled', true)
            ->where('api_web_services.code', $service->code)
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
     * access is obtained: `global` (the "all domains" scope), `direct`
     * (an enabled permission row), or `none` when there is no access.
     */
    public function effectiveAccessState(WebService $service): array
    {
        $directRow = $this->webServices()
            ->where('api_web_services.code', $service->code)
            ->first();

        $directEnabled = $this->hasDirectPermissionFor($service);
        $overrideDisabled = $directRow !== null && ! $directRow->pivot->is_enabled;
        $active = $this->isActive() && $service->is_active;
        $globalScope = $this->hasGlobalAccessScope();

        // An explicit disabled override always wins — even over global scope.
        if ($overrideDisabled) {
            $effective = false;
        } else {
            $effective = $active && ($globalScope || $directEnabled);
        }

        if (! $effective) {
            $source = 'none';
        } elseif ($globalScope) {
            $source = 'global';
        } else {
            $source = 'direct';
        }

        return [
            'code' => $service->code,
            'name' => $service->name,
            'description' => $service->description,
            'global_is_active' => $service->is_active,
            'is_enabled' => $directRow !== null ? (bool) $directRow->pivot->is_enabled : false,
            'access_scope' => $this->access_scope ?? self::ACCESS_SCOPE_SELECTED,
            'override_disabled' => $overrideDisabled,
            'grant_source' => $source,
            'effective_access' => $effective,
        ];
    }
}
