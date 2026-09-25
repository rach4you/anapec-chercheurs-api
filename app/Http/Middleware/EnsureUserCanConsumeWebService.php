<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\WebService;
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
     * 3. The requested Web Service exists and is globally active
     * 4. The user has effective access to that Web Service
     *    (direct permission, an enabled active domain containing the
     *    service, or both — an explicit disabled override always wins)
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

        $webService = WebService::query()
            ->where('code', $webServiceCode)
            ->first();

        if (! $webService) {
            return $this->deny('Unknown Web Service.');
        }

        if (! $webService->is_active) {
            return $this->deny('Web Service is disabled.');
        }

        if (! $user->hasEffectiveAccessTo($webService)) {
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
