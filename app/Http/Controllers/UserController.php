<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WebService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends ApiJsonController
{
    /**
     * Reset the password of the currently authenticated user.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string', 'min:8'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        /** @var User $user */
        $user = $request->user('api');

        if (! auth('api')->validate(['email' => $user->email, 'password' => $request->input('current_password')])) {
            return $this->error('Current password is incorrect.', null, 422);
        }

        $user->update([
            'password' => $request->input('password'),
        ]);

        $user->currentAccessToken()->delete();

        return $this->success('Password has been reset. Please log in again.');
    }

    /**
     * List the current user's Web Service permissions (read-only).
     *
     * Returns ALL registered Web Services so the user can see which exist,
     * which are globally active, whether they hold a permission, and whether
     * access is effectively available. Uses only the authenticated user —
     * no user_id parameter is accepted.
     */
    public function webServices(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        $services = WebService::query()->orderBy('code')->get();

        $data = $services
            ->map(fn (WebService $service) => $user->effectiveAccessState($service))
            ->values();

        return $this->success('Web Services retrieved.', $data);
    }
}
