<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\ToggleWebServiceStatusRequest;
use App\Http\Requests\Admin\UpdateWebServiceRequest;
use App\Http\Resources\WebServiceResource;
use App\Models\WebService;
use Illuminate\Http\JsonResponse;

class WebServiceController extends ApiJsonController
{
    /**
     * List all Web Services (admin-only).
     */
    public function index(): JsonResponse
    {
        $services = WebService::query()->with('domains')->orderBy('code')->get();

        return $this->success('Web Services retrieved.', WebServiceResource::collection($services));
    }

    /**
     * Retrieve a single Web Service by code (admin-only).
     */
    public function show(string $code): JsonResponse
    {
        $service = WebService::query()->where('code', $code)->with('domains')->firstOrFail();

        return $this->success('Web Service retrieved.', new WebServiceResource($service));
    }

    /**
     * Update a Web Service's updatable metadata (admin-only).
     *
     * The `code` is a technical identifier and is never updatable, and
     * the global ON/OFF status is handled by its dedicated `toggleStatus`
     * endpoint, so only `name` and `description` are applied.
     */
    public function update(UpdateWebServiceRequest $request, string $code): JsonResponse
    {
        $service = WebService::query()->where('code', $code)->firstOrFail();

        $service->update([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
        ]);

        return $this->success('Web Service updated.', new WebServiceResource($service->fresh()));
    }

    /**
     * Toggle a Web Service's global active/inactive status (admin-only).
     */
    public function toggleStatus(ToggleWebServiceStatusRequest $request, string $code): JsonResponse
    {
        $service = WebService::query()->where('code', $code)->firstOrFail();

        $service->is_active = filter_var($request->validated('is_active'), FILTER_VALIDATE_BOOL);
        $service->save();

        return $this->success('Web Service status updated successfully.', [
            'code' => $service->code,
            'is_active' => $service->is_active,
        ]);
    }
}
