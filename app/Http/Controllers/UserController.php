<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
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
}
