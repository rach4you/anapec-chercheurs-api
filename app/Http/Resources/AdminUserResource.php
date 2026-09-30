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
 * Carries the base identity fields plus the two derived access indicators
 * the list table displays:
 *   - web_services_count: configured services (direct permission rows plus
 *     services granted through an enabled, active domain)
 *   - effective_services_count: services the user can actually consume now
 *     (user active AND service globally active)
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
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
            'web_services_count' => $configured->count(),
            'effective_services_count' => $effective->count(),
            'last_activity_at' => $user->lastActivityAt()?->toIso8601String(),
        ];
    }
}
