<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\AssignDomainServicesRequest;
use App\Http\Requests\Admin\CreateDomainRequest;
use App\Http\Requests\Admin\ToggleDomainStatusRequest;
use App\Http\Requests\Admin\UpdateDomainRequest;
use App\Http\Resources\WebServiceDomainResource;
use App\Models\WebService;
use App\Models\WebServiceDomain;
use Illuminate\Http\JsonResponse;

class WebServiceDomainController extends ApiJsonController
{
    /**
     * List all Web Service domains (admin-only).
     */
    public function index(): JsonResponse
    {
        $domains = WebServiceDomain::query()
            ->withCount('webServices')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        return $this->success('Web Service domains retrieved.', WebServiceDomainResource::collection($domains));
    }

    /**
     * Retrieve a single Web Service domain by code (admin-only).
     */
    public function show(string $code): JsonResponse
    {
        $domain = WebServiceDomain::query()->where('code', $code)->firstOrFail();

        $payload = (new WebServiceDomainResource($domain))->resolve();

        $payload['web_services'] = $domain->webServices()
            ->orderBy('api_web_services.code')
            ->get()
            ->map(fn (WebService $service) => $service->code)
            ->values();

        return $this->success('Web Service domain retrieved.', $payload);
    }

    /**
     * Create a Web Service domain (admin-only).
     */
    public function store(CreateDomainRequest $request): JsonResponse
    {
        $domain = WebServiceDomain::query()->create([
            'code' => $request->validated('code'),
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'is_active' => $request->validated('is_active', true),
            'sort_order' => $request->validated('sort_order', 0),
        ]);

        return $this->success('Web Service domain created.', new WebServiceDomainResource($domain), 201);
    }

    /**
     * Update a Web Service domain's updatable metadata (admin-only).
     *
     * The `code` is a technical identifier and is intentionally immutable.
     * The ON/OFF status has its own dedicated endpoint and must not be
     * mixed into metadata updates.
     */
    public function update(UpdateDomainRequest $request, string $code): JsonResponse
    {
        $domain = WebServiceDomain::query()->where('code', $code)->firstOrFail();

        $domain->update([
            'name' => $request->validated('name', $domain->name),
            'description' => $request->validated('description', $domain->description),
            'sort_order' => $request->validated('sort_order', $domain->sort_order),
        ]);

        return $this->success('Web Service domain updated.', new WebServiceDomainResource($domain->fresh()));
    }

    /**
     * Toggle a Web Service domain's active/inactive status (admin-only).
     */
    public function toggleStatus(ToggleDomainStatusRequest $request, string $code): JsonResponse
    {
        $domain = WebServiceDomain::query()->where('code', $code)->firstOrFail();

        $domain->is_active = filter_var($request->validated('is_active'), FILTER_VALIDATE_BOOL);
        $domain->save();

        return $this->success('Web Service domain status updated.', [
            'code' => $domain->code,
            'is_active' => $domain->is_active,
        ]);
    }

    /**
     * Replace the Web Services belonging to a domain (admin-only).
     *
     * Full replacement: removes every membership row then re-creates the
     * provided set. An empty list is rejected (a domain must keep at least
     * one member, matching the Web Service permission semantics).
     */
    public function assignServices(AssignDomainServicesRequest $request, string $code): JsonResponse
    {
        $domain = WebServiceDomain::query()->where('code', $code)->firstOrFail();

        $codes = $request->validated('web_services');

        $services = WebService::query()->whereIn('code', $codes)->pluck('code')->all();

        $domain->webServices()->detach();

        foreach ($services as $serviceCode) {
            $service = WebService::query()->where('code', $serviceCode)->firstOrFail();
            $domain->webServices()->attach($service->id);
        }

        return $this->success('Domain services updated.', [
            'code' => $domain->code,
            'web_services' => $services,
        ]);
    }
}
