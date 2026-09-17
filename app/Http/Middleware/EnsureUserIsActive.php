<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Reject inactive users even if their token is still valid.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user('api');

        if ($user && ! $user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Account is deactivated.',
            ], 401);
        }

        return $next($request);
    }
}
