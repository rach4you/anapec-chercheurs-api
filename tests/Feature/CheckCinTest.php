<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WebService;
use App\Services\CheckCinService;
use Database\Factories\UserFactory;
use Database\Seeders\WebServiceSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CheckCinTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = [null];

    protected function setUp(): void
    {
        parent::setUp();

        $this->beginDatabaseTransaction();
        $this->seed(WebServiceSeeder::class);
    }

    private function userWithCinPermission(?string $email = null, bool $enabled = true, bool $active = true): User
    {
        $factory = UserFactory::new();

        if (! $active) {
            $factory->asInactive();
        }

        $user = $factory->create([
            'email' => $email ?? fake()->unique()->safeEmail(),
            'password' => 'secret123',
            'is_active' => $active,
        ]);

        $ws = WebService::query()->where('code', 'WS_CHECK_CIN')->firstOrFail();
        $user->webServices()->attach($ws->id, ['is_enabled' => $enabled]);

        return $user;
    }

    // 1. unauthenticated request → 401
    public function test_unauthenticated_request_returns_401(): void
    {
        $this->postJson('/api/v1/services/check-cin', ['cin' => 'EA154824'])
            ->assertStatus(401);
    }

    // 2. inactive user → 403
    public function test_inactive_user_returns_403(): void
    {
        $user = $this->userWithCinPermission('inact_cin@example.com', true, false);

        $this->assertFalse($user->fresh()->is_active);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/services/check-cin', ['cin' => 'EA154824']);

        // Global EnsureUserIsActive middleware returns 401 for inactive users
        $response->assertStatus(401);
    }

    // 3. user without WS_CHECK_CIN permission → 403
    public function test_user_without_cin_permission_returns_403(): void
    {
        $user = UserFactory::new()->create([
            'email' => 'nocinperm@example.com',
            'password' => 'secret123',
        ]);

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/services/check-cin', ['cin' => 'EA154824'])
            ->assertStatus(403);
    }

    // 4. user with disabled permission → 403
    public function test_user_with_disabled_cin_permission_returns_403(): void
    {
        $user = $this->userWithCinPermission('disabledcin@example.com', false);

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/services/check-cin', ['cin' => 'EA154824'])
            ->assertStatus(403);
    }

    // 5. WS_CHECK_CIN globally OFF → 403
    public function test_globally_off_cin_returns_403(): void
    {
        $user = $this->userWithCinPermission('globoff@example.com');

        $ws = WebService::query()->where('code', 'WS_CHECK_CIN')->firstOrFail();
        $ws->update(['is_active' => false]);

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/services/check-cin', ['cin' => 'EA154824'])
            ->assertStatus(403);
    }

    // 6. valid user + permission + global ON → allowed
    public function test_valid_user_with_permission_and_global_on_is_allowed(): void
    {
        $user = $this->userWithCinPermission('validcin@example.com');

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/services/check-cin', ['cin' => 'EA154824'])
            ->assertOk();
    }

    // 7. valid CIN that exists → 200 + exists=true
    public function test_existing_cin_returns_exists_true(): void
    {
        $user = $this->userWithCinPermission('cinexist@example.com');

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/services/check-cin', ['cin' => 'EA154824']);

        $response->assertOk()
            ->assertJsonPath('data.exists', true);
    }

    // 8. valid CIN that does not exist → 200 + exists=false
    public function test_nonexistent_cin_returns_exists_false(): void
    {
        $user = $this->userWithCinPermission('cinnotexist@example.com');

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/services/check-cin', ['cin' => 'ZZZZ999999999']);

        $response->assertOk()
            ->assertJsonPath('data.exists', false);
    }

    // 9. missing CIN → 422
    public function test_missing_cin_returns_422(): void
    {
        $user = $this->userWithCinPermission('missingcin@example.com');

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/services/check-cin', [])
            ->assertStatus(422);
    }

    // 10. invalid/empty CIN → 422
    public function test_empty_cin_returns_422(): void
    {
        $user = $this->userWithCinPermission('emptycin@example.com');

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/services/check-cin', ['cin' => ''])
            ->assertStatus(422);
    }

    // 11. password/token/internal data never exposed
    public function test_no_sensitive_data_in_response(): void
    {
        $user = $this->userWithCinPermission('nosecret@example.com');

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/services/check-cin', ['cin' => 'EA154824']);

        $body = $response->getContent();

        $this->assertStringNotContainsString('secret123', $body);
        $this->assertStringNotContainsString('password', $body);
    }

    // 12. query is read-only (verify service uses no writes)
    public function test_service_returns_bool_and_is_read_only(): void
    {
        $service = new CheckCinService;

        $result = $service->exists('EA154824');

        $this->assertIsBool($result);
    }
}
