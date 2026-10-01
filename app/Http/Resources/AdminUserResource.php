<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 *
 * Extended payload for the admin "Utilisateurs" list page.
 *
 * Carries the base identity fields plus the derived access indicators the
 * list table displays:
 *   - access_scope: "all" (every active Web Service in every domain) or
 *     "selected" (only the explicitly enabled Web Services)
 *   - web_services_count: number of Web Services configured for the user
 *     (dynamic for scope "all")
 *   - active_services_total: number of ACTIVE Web Services in the catalog,
 *     the denominator of "Accès effectif"
 *   - effective_services_count: ACTIVE Web Services the user can actually
 *     consume now
 *   - last_activity_at: most recent API session activity
 */
class AdminUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $user = $this;

        $configured = $user->configuredServiceCodes();
        $effective = $user->effectiveServiceCodes();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'access_scope' => $user->access_scope ?? User::ACCESS_SCOPE_SELECTED,
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
            'web_services_count' => $configured->count(),
            'active_services_total' => $user->activeServiceCount(),
            'effective_services_count' => $effective->count(),
            'last_activity_at' => $user->lastActivityAt()?->toIso8601String(),
        ];
    }
}
