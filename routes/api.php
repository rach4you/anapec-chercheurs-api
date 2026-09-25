<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Api\DocumentationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Services\CheckCinController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebServiceController;
use App\Http\Controllers\WebServiceDomainController;
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
        Route::get('user/web-services', [UserController::class, 'webServices']);

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
            Route::delete('users/{id}/web-services', [AdminUserController::class, 'revokeAllWebServices']);
            Route::patch('users/{id}/web-services/{code}', [AdminUserController::class, 'updateWebServicePermission']);
            Route::get('users/{id}/domains', [AdminUserController::class, 'domains']);
            Route::put('users/{id}/domains', [AdminUserController::class, 'assignDomains']);
            Route::patch('users/{id}/domains/{code}', [AdminUserController::class, 'updateDomainPermission']);

            Route::get('web-services', [WebServiceController::class, 'index']);
            Route::get('web-services/{code}', [WebServiceController::class, 'show']);
            Route::patch('web-services/{code}', [WebServiceController::class, 'update']);
            Route::patch('web-services/{code}/status', [WebServiceController::class, 'toggleStatus']);

            Route::get('domains', [WebServiceDomainController::class, 'index']);
            Route::post('domains', [WebServiceDomainController::class, 'store']);
            Route::get('domains/{code}', [WebServiceDomainController::class, 'show']);
            Route::patch('domains/{code}', [WebServiceDomainController::class, 'update']);
            Route::patch('domains/{code}/status', [WebServiceDomainController::class, 'toggleStatus']);
            Route::put('domains/{code}/services', [WebServiceDomainController::class, 'assignServices']);
        });
    });
});
