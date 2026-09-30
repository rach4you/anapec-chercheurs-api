<?php

namespace App\Http\Resources;

use App\Models\WebServiceDomain;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WebServiceDomain
 */
class WebServiceDomainResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $serviceCount = (int) ($this->webServices_count ?? $this->webServices()->count());

        $webServices = $this->whenLoaded('webServices', fn () => $this->webServices, []);

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'service_count' => $serviceCount,
            'web_services' => collect($webServices)->map(fn ($ws) => [
                'code' => $ws->code,
                'name' => $ws->name,
                'is_active' => $ws->is_active,
            ])->values(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
