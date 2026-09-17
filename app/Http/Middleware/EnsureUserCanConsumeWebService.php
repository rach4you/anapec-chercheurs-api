<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanConsumeWebService
{
    /**
     * Handle an incoming request.
     *
     * Verify that:
     * 1. The user is authenticated
     * 2. The user is active
     * 3. The user's role allows consumption
     * 4. The requested Web Service is active
     * 5. The user has a permission record for that Web Service
     * 6. The permission is enabled
     */
    public function handle(Request $request, Closure $next, string $webServiceCode)
    {
        /** @var User|null $user */
        $user = $request->user('api');

        if (! $user) {
            return $this->deny('Unauthenticated.');
        }

        if (! $user->isActive()) {
            return $this->deny('Account is deactivated.');
        }

        $webService = \App\Models\WebService::query()
            ->where('code', $webServiceCode)
            ->first();

        if (! $webService) {
            return $this->deny('Unknown Web Service.');
        }

        if (! $webService->is_active) {
            return $this->deny('Web Service is disabled.');
        }

        if (! $user->canConsumeWebService($webServiceCode)) {
            return $this->deny('Insufficient permissions.');
        }

        return $next($request);
    }

    /**
     * Return a 403 JSON response without leaking internal details.
     */
    private function deny(string $message): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 403);
    }
}
