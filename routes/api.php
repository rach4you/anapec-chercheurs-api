<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Api\DocumentationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Services\CheckCinController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebServiceController;
use Illuminate\Support\Facades\Route;

// ── Swagger / OpenAPI Documentation (public) ──
Route::get('documentation', [DocumentationController::class, 'ui']);
Route::get('documentation/openapi', [DocumentationController::class, 'openapi']);

Route::prefix('v1')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware(['auth:api'])->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::patch('user/password', [UserController::class, 'updatePassword']);

        Route::middleware(['ws:WS_CHECK_CIN', 'throttle:60,1'])
            ->post('services/check-cin', [CheckCinController::class, 'check']);

        Route::middleware(['can:admin'])->prefix('admin')->group(function (): void {
            Route::get('users', [AdminUserController::class, 'index']);
            Route::post('users', [AdminUserController::class, 'store']);
            Route::get('users/{id}', [AdminUserController::class, 'show']);
            Route::put('users/{id}', [AdminUserController::class, 'update']);
            Route::patch('users/{id}/status', [AdminUserController::class, 'toggleStatus']);
            Route::post('users/{id}/password', [AdminUserController::class, 'resetPassword']);
            Route::get('users/{id}/web-services', [AdminUserController::class, 'webServices']);
            Route::put('users/{id}/web-services', [AdminUserController::class, 'assignWebServices']);

            Route::get('web-services', [WebServiceController::class, 'index']);
            Route::get('web-services/{code}', [WebServiceController::class, 'show']);
            Route::patch('web-services/{code}/status', [WebServiceController::class, 'toggleStatus']);
        });
    });
});
