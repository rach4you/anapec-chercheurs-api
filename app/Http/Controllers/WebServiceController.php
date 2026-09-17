<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\ToggleWebServiceStatusRequest;
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
        $services = WebService::query()->orderBy('code')->get();

        return $this->success('Web Services retrieved.', WebServiceResource::collection($services));
    }

    /**
     * Retrieve a single Web Service by code (admin-only).
     */
    public function show(string $code): JsonResponse
    {
        $service = WebService::query()->where('code', $code)->firstOrFail();

        return $this->success('Web Service retrieved.', new WebServiceResource($service));
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
