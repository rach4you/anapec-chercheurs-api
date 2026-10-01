<?php

namespace App\Http\Controllers\Services;

use App\Exceptions\DuplicateCinException;
use App\Exceptions\DuplicateEmailException;
use App\Http\Controllers\ApiJsonController;
use App\Http\Requests\Services\InscriptionRequest;
use App\Services\InscriptionService;
use Illuminate\Http\JsonResponse;

class InscriptionController extends ApiJsonController
{
    public function __construct(
        private readonly InscriptionService $inscriptionService
    ) {}

    /**
     * Fully register a researcher and all their associated rows.
     *
     * Runs in a single DB transaction. On any failure, every insert is rolled
     * back and no partial data survives in SIGEC.
     */
    public function store(InscriptionRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $result = $this->inscriptionService->register($data);
        } catch (DuplicateCinException) {
            return response()->json([
                'success' => false,
                'message' => 'Un chercheur existe déjà avec ce CIN.',
                'code' => 'CIN_ALREADY_EXISTS',
            ], 409);
        } catch (DuplicateEmailException) {
            return response()->json([
                'success' => false,
                'message' => 'Un chercheur existe déjà avec cet email.',
                'code' => 'EMAIL_ALREADY_EXISTS',
            ], 409);
        }

        return $this->success('Chercheur enregistré avec succès.', $result, 201);
    }
}
