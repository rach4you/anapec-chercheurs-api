<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\ApiJsonController;
use App\Http\Requests\Services\CheckCinRequest;
use App\Services\CheckCinService;
use Illuminate\Http\JsonResponse;

class CheckCinController extends ApiJsonController
{
    public function __construct(
        private readonly CheckCinService $checkCinService
    ) {}

    /**
     * Verify whether a researcher exists by CIN.
     */
    public function check(CheckCinRequest $request): JsonResponse
    {
        $cin = $request->validated('cin');

        $exists = $this->checkCinService->exists($cin);

        return $this->success('CIN check completed.', [
            'exists' => $exists,
        ]);
    }
}
