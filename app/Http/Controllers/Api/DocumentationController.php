<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\Yaml\Yaml;

class DocumentationController
{
    /**
     * Serve Swagger UI at /api/documentation.
     */
    public function ui(): View
    {
        return view('api.swagger');
    }

    /**
     * Serve the OpenAPI YAML spec as JSON at /api/documentation/openapi.
     */
    public function openapi(): JsonResponse
    {
        $yaml = \File::get(\storage_path('openapi/openapi.yaml'));
        $spec = Yaml::parse($yaml);

        return response()->json($spec, 200, ['Content-Type' => 'application/json']);
    }
}
