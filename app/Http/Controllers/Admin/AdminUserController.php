<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiJsonController;
use App\Http\Requests\Admin\AssignUserDomainsRequest;
use App\Http\Requests\Admin\AssignWebServicesRequest;
use App\Http\Requests\Admin\CreateUserRequest;
use App\Http\Requests\Admin\ToggleUserStatusRequest;
use App\Http\Requests\Admin\UpdateUserDomainPermissionRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Requests\Admin\UpdateUserWebServicePermissionRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserDomain;
use App\Models\UserWebService;
use App\Models\WebService;
use App\Models\WebServiceDomain;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUserController extends ApiJsonController
{
    /**
     * List all API users.
     */
    public function index(): JsonResponse
    {
        $users = User::query()->orderBy('name')->get();

        return $this->success('Users retrieved.', new UserCollection($users));
    }

    /**
     * Retrieve a single API user.
     */
    public function show(int $id): JsonResponse
    {
        $user = User::query()->findOrFail($id);

        return $this->success('User retrieved.', new UserResource($user));
    }

    /**
     * Create an API user.
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'role' => $request->validated('role', User::ROLE_USER),
            'is_active' => $request->validated('is_active', true),
        ]);

        return $this->success('User created.', new UserResource($user), 201);
    }

    /**
     * Update an API user.
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = User::query()->findOrFail($id);

        $input = $request->validated();

        // A user cannot change their own role; only an admin can.
        $actor = $request->user('api');
        if (array_key_exists('role', $input)
            && $actor
            && ! $actor->isAdmin()
            && (int) $user->id === (int) $actor->id
        ) {
            return $this->error('You cannot change your own role.', null, 403);
        }

        $user->update([
            'name' => $input['name'] ?? $user->name,
            'email' => $input['email'] ?? $user->email,
            'password' => $input['password'] ?? $user->password,
            'role' => $input['role'] ?? $user->role,
        ]);

        return $this->success('User updated.', new UserResource($user->fresh()));
    }

    /**
     * Toggle an API user's active/inactive status.
     */
    public function toggleStatus(ToggleUserStatusRequest $request, int $id): JsonResponse
    {
        $user = User::query()->findOrFail($id);

        $user->is_active = filter_var($request->validated('is_active'), FILTER_VALIDATE_BOOL);
        $user->save();

        return $this->success('User status updated.', new UserResource($user->fresh()));
    }

    /**
     * Reset an API user's password.
     */
    public function resetPassword(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::query()->findOrFail($id);

        $user->update([
            'password' => $request->validated('password'),
        ]);

        return $this->success('User password has been reset.');
    }

    /**
     * List the Web Services assigned to a user.
     */
    public function webServices(int $id): JsonResponse
    {
        $user = User::query()->findOrFail($id);

        $pivot = UserWebService::query()
            ->where('user_id', $user->id)
            ->with('webService')
            ->get()
            ->map(function (UserWebService $row) use ($user) {
                $state = $user->effectiveAccessState($row->webService);

                return [
                    'code' => $state['code'],
                    'name' => $state['name'],
                    'global_is_active' => $state['global_is_active'],
                    'is_enabled' => $state['is_enabled'],
                    'domain_granted' => $state['domain_granted'],
                    'override_disabled' => $state['override_disabled'],
                    'grant_source' => $state['grant_source'],
                    'effective_access' => $state['effective_access'],
                ];
            });

        return $this->success('Web Services retrieved.', $pivot);
    }

    /**
     * Assign Web Services to a user.
     */
    public function assignWebServices(AssignWebServicesRequest $request, int $id): JsonResponse
    {
        $user = User::query()->findOrFail($id);

        $assignments = $request->validated('web_services');

        UserWebService::query()
            ->where('user_id', $user->id)
            ->delete();

        foreach ($assignments as $item) {
            $webService = WebService::query()->where('code', $item['code'])->firstOrFail();
            $enabled = (bool) ($item['enabled'] ?? true);

            UserWebService::query()->create([
                'user_id' => $user->id,
                'web_service_id' => $webService->id,
                'is_enabled' => $enabled,
            ]);
        }

        return $this->success('Web Services assigned.');
    }

    /**
     * Enable or disable a single Web Service permission for a user.
     */
    public function updateWebServicePermission(UpdateUserWebServicePermissionRequest $request, int $id, string $code): JsonResponse
    {
        $user = User::query()->findOrFail($id);

        $enabled = filter_var($request->validated('is_enabled'), FILTER_VALIDATE_BOOL);

        try {
            $result = DB::transaction(function () use ($user, $code, $enabled): array {
                $service = WebService::query()->where('code', $code)->first();

                if (! $service) {
                    return ['not_found' => true];
                }

                // Enabling access requires the Web Service to be globally active.
                // Disabling is always allowed, even for an inactive service:
                // access can be withdrawn from a service that is no longer offered.
                if ($enabled && ! $service->is_active) {
                    return ['not_active' => true];
                }

                $row = UserWebService::query()
                    ->where('user_id', $user->id)
                    ->where('web_service_id', $service->id)
                    ->first();

                // Disabling a permission that was never granted does not need a
                // meaningless disabled row: absence already means "no access".
                if (! $row && ! $enabled) {
                    return [
                        'unchanged' => true,
                        'service' => $service,
                        'row' => null,
                    ];
                }

                if ($row && $row->is_enabled === $enabled) {
                    return [
                        'unchanged' => true,
                        'service' => $service,
                        'row' => $row,
                    ];
                }

                if ($row) {
                    $row->is_enabled = $enabled;
                    $row->updated_at = now();
                    $row->save();

                    return [
                        'unchanged' => false,
                        'service' => $service,
                        'row' => $row,
                    ];
                }

                UserWebService::query()->create([
                    'user_id' => $user->id,
                    'web_service_id' => $service->id,
                    'is_enabled' => $enabled,
                ]);

                $created = UserWebService::query()
                    ->where('user_id', $user->id)
                    ->where('web_service_id', $service->id)
                    ->first();

                return [
                    'unchanged' => false,
                    'service' => $service,
                    'row' => $created,
                ];
            });
        } catch (UniqueConstraintViolationException) {
            // A concurrent request created the row between our read and write.
            // Converge on the row it created instead of failing the request.
            $result = DB::transaction(function () use ($user, $code, $enabled): array {
                $service = WebService::query()->where('code', $code)->firstOrFail();

                $row = UserWebService::query()
                    ->where('user_id', $user->id)
                    ->where('web_service_id', $service->id)
                    ->first();

                $unchanged = $row !== null && $row->is_enabled === $enabled;

                if (! $unchanged) {
                    $row->is_enabled = $enabled;
                    $row->updated_at = now();
                    $row->save();
                }

                return [
                    'unchanged' => $unchanged,
                    'service' => $service,
                    'row' => $row,
                ];
            });
        }

        if (isset($result['not_found'])) {
            return $this->error("Web Service '{$code}' does not exist.", null, 404);
        }

        if (isset($result['not_active'])) {
            return $this->error("Web Service '{$code}' is not active.", null, 422);
        }

        $service = $result['service'];

        $state = $user->effectiveAccessState($service);

        return $this->success($result['unchanged'] ? 'Permission unchanged.' : 'Permission updated.', [
            'code' => $state['code'],
            'name' => $state['name'],
            'description' => $state['description'],
            'global_is_active' => $state['global_is_active'],
            'is_enabled' => $state['is_enabled'],
            'domain_granted' => $state['domain_granted'],
            'override_disabled' => $state['override_disabled'],
            'grant_source' => $state['grant_source'],
            'effective_access' => $state['effective_access'],
        ]);
    }

    /**
     * Revoke every Web Service permission held by a user.
     */
    public function revokeAllWebServices(Request $request, int $id): JsonResponse
    {
        $user = User::query()->findOrFail($id);

        DB::transaction(function () use ($user): void {
            UserWebService::query()
                ->where('user_id', $user->id)
                ->delete();
        });

        return $this->success('Web Services revoked.');
    }

    /**
     * List the Web Service domains assigned to a user.
     *
     * Reports, for every domain, the user's grant flag and the count of
     * services the domain currently contains. This is the "normal" grant
     * path; `api_user_web_services` rows remain the override channel.
     */
    public function domains(int $id): JsonResponse
    {
        $user = User::query()->findOrFail($id);

        $rows = UserDomain::query()
            ->where('user_id', $user->id)
            ->with('domain')
            ->get()
            ->map(function (UserDomain $row) {
                return [
                    'code' => $row->domain->code,
                    'name' => $row->domain->name,
                    'domain_is_active' => $row->domain->is_active,
                    'service_count' => $row->domain->webServices()->count(),
                    'is_enabled' => $row->is_enabled,
                ];
            });

        $services = WebService::query()->orderBy('code')->get()
            ->map(fn (WebService $service) => $user->effectiveAccessState($service))
            ->values();

        return $this->success('Domains and effective Web Service states retrieved.', [
            'domains' => $rows,
            'web_services' => $services,
        ]);
    }

    /**
     * Assign Web Service domains to a user (full replace).
     *
     * Replaces the user's entire set of domain grants with the provided list.
     * Does not touch `api_user_web_services` override rows.
     */
    public function assignDomains(AssignUserDomainsRequest $request, int $id): JsonResponse
    {
        $user = User::query()->findOrFail($id);

        $codes = $request->validated('domains');

        DB::transaction(function () use ($user, $codes): void {
            UserDomain::query()
                ->where('user_id', $user->id)
                ->delete();

            $domains = WebServiceDomain::query()
                ->whereIn('code', $codes)
                ->pluck('code')
                ->all();

            foreach ($domains as $domainCode) {
                $domain = WebServiceDomain::query()->where('code', $domainCode)->firstOrFail();
                $user->domains()->attach($domain->id);
            }
        });

        return $this->success('Domains assigned.');
    }

    /**
     * Enable or disable a single domain grant for a user.
     */
    public function updateDomainPermission(UpdateUserDomainPermissionRequest $request, int $id, string $code): JsonResponse
    {
        $user = User::query()->findOrFail($id);
        $domain = WebServiceDomain::query()->where('code', $code)->first();

        if (! $domain) {
            return $this->error("Domain '{$code}' does not exist.", null, 404);
        }

        $enabled = filter_var($request->validated('is_enabled'), FILTER_VALIDATE_BOOL);

        $row = UserDomain::query()
            ->where('user_id', $user->id)
            ->where('domain_id', $domain->id)
            ->first();

        // Absence already means "no grant"; a disable of a missing row is a no-op.
        if (! $row && ! $enabled) {
            return $this->success('Permission unchanged.', [
                'code' => $domain->code,
                'name' => $domain->name,
                'is_enabled' => false,
            ]);
        }

        if ($row && $row->is_enabled === $enabled) {
            return $this->success('Permission unchanged.', [
                'code' => $domain->code,
                'name' => $domain->name,
                'is_enabled' => $enabled,
            ]);
        }

        if ($row) {
            $row->is_enabled = $enabled;
            $row->updated_at = now();
            $row->save();
        } else {
            $user->domains()->attach($domain->id, ['is_enabled' => $enabled]);
        }

        return $this->success('Permission updated.', [
            'code' => $domain->code,
            'name' => $domain->name,
            'domain_is_active' => $domain->is_active,
            'service_count' => $domain->webServices()->count(),
            'is_enabled' => $enabled,
        ]);
    }
}
