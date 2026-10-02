<?php

namespace App\Http\Controllers\Services;

use App\Exceptions\CinNotFoundException;
use App\Exceptions\DuplicateEmailException;
use App\Http\Controllers\ApiJsonController;
use App\Http\Requests\Services\ActualisationRequest;
use App\Services\ActualisationService;
use Illuminate\Http\JsonResponse;

class ActualisationController extends ApiJsonController
{
    public function __construct(
        private readonly ActualisationService $actualisationService
    ) {}

    /**
     * Update an existing researcher and all provided relation rows.
     *
     * Runs in a single DB transaction. On any failure, every update and
     * relation change is rolled back — no partial data survives in SIGEC.
     */
    public function update(ActualisationRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $result = $this->actualisationService->update($data);
        } catch (CinNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun chercheur trouvé avec ce CIN.',
                'code' => 'CIN_NOT_FOUND',
            ], 404);
        } catch (DuplicateEmailException) {
            return response()->json([
                'success' => false,
                'message' => 'Un chercheur existe déjà avec cet email.',
                'code' => 'EMAIL_ALREADY_EXISTS',
            ], 409);
        }

        return $this->success('Chercheur actualisé avec succès.', $result);
    }
}
