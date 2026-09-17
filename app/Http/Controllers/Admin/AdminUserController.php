<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiJsonController;
use App\Http\Requests\Admin\AssignWebServicesRequest;
use App\Http\Requests\Admin\CreateUserRequest;
use App\Http\Requests\Admin\ToggleUserStatusRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserWebService;
use App\Models\WebService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            ->map(function (UserWebService $row) {
                return [
                    'code' => $row->webService->code,
                    'name' => $row->webService->name,
                    'is_enabled' => $row->is_enabled,
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
}
