<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserWebService;
use App\Models\WebService;
use Database\Factories\UserFactory;
use Database\Seeders\WebServiceSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Phase 4.1 — a regular user's read-only view of their own Web Service
 * permissions (GET /api/v1/user/web-services).
 *
 * Uses the project's established test setup: a live Oracle schema wrapped in
 * a per-test transaction that is rolled back, plus the idempotent seeder so
 * the four canonical Web Services are present. No schema change is required.
 */
class WebServiceUserOwnTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = [null];

    protected function setUp(): void
    {
        parent::setUp();

        $this->beginDatabaseTransaction();
        $this->seed(WebServiceSeeder::class);
    }

    private function user(): User
    {
        return UserFactory::new()->create(['email' => 'ws-own-user@example.com', 'password' => 'secret123']);
    }

    private function admin(): User
    {
        return UserFactory::new()->asAdmin()->create(['email' => 'ws-own-admin@example.com', 'password' => 'secret123']);
    }

    private function attach(User $user, string $code, bool $enabled = true): void
    {
        $ws = WebService::query()->where('code', $code)->firstOrFail();
        $user->webServices()->attach($ws->id, ['is_enabled' => $enabled]);
    }

    // A regular user can view their own permissions.
    public function test_regular_user_can_view_own_permissions(): void
    {
        $user = $this->user();

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/user/web-services');

        $response->assertOk()->assertJsonPath('success', true);

        // The user has no permissions yet, but all registered services are listed.
        $data = $response->json('data');
        $this->assertCount(4, $data);
        foreach ($data as $row) {
            $this->assertFalse($row['is_enabled']);
            $this->assertFalse($row['effective_access']);
        }
    }

    // An admin can view their own permissions through the same user endpoint.
    public function test_admin_can_view_own_permissions_via_user_endpoint(): void
    {
        $admin = $this->admin();
        $this->attach($admin, 'WS_CV');

        $response = $this->actingAs($admin, 'api')->getJson('/api/v1/user/web-services');

        $cv = collect($response->json('data'))->firstWhere('code', 'WS_CV');
        $this->assertTrue($cv['is_enabled']);
        $this->assertTrue($cv['effective_access']);
    }

    // An unauthenticated request is rejected.
    public function test_unauthenticated_user_receives_401(): void
    {
        $this->getJson('/api/v1/user/web-services')->assertStatus(401);
    }

    // An inactive user follows the existing inactive-user protection (401).
    public function test_inactive_user_follows_existing_protection(): void
    {
        $user = UserFactory::new()->asInactive()->create(['email' => 'ws-own-inactive@example.com', 'password' => 'secret123']);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/user/web-services')
            ->assertStatus(401);
    }

    // The endpoint accepts no user_id parameter: a foreign user_id is ignored.
    public function test_endpoint_ignores_foreign_user_id(): void
    {
        $attacker = $this->user();
        $victim = UserFactory::new()->create(['email' => 'ws-own-victim@example.com', 'password' => 'secret123']);
        $this->attach($victim, 'WS_CV');

        $response = $this->actingAs($attacker, 'api')
            ->getJson('/api/v1/user/web-services?user_id='.$victim->id);

        $cv = collect($response->json('data'))->firstWhere('code', 'WS_CV');
        $this->assertFalse($cv['is_enabled'], 'A foreign user_id must not leak another user\'s permissions');
        $this->assertFalse($cv['effective_access']);
    }

    // The response carries the expected fields.
    public function test_response_includes_expected_fields(): void
    {
        $user = $this->user();
        $this->attach($user, 'WS_CHECK_CIN');

        $data = $this->actingAs($user, 'api')->getJson('/api/v1/user/web-services')->json('data');
        $row = collect($data)->firstWhere('code', 'WS_CHECK_CIN');

        $this->assertArrayHasKey('code', $row);
        $this->assertArrayHasKey('name', $row);
        $this->assertArrayHasKey('description', $row);
        $this->assertArrayHasKey('global_is_active', $row);
        $this->assertArrayHasKey('is_enabled', $row);
        $this->assertArrayHasKey('effective_access', $row);
        $this->assertSame('WS_CHECK_CIN', $row['code']);
    }

    // effective_access is true only when user active AND service active AND permission enabled.
    public function test_effective_access_requires_all_three_conditions(): void
    {
        $user = $this->user();
        $this->attach($user, 'WS_CV');

        // All three true → effective.
        $row = collect($this->actingAs($user, 'api')->getJson('/api/v1/user/web-services')->json('data'))
            ->firstWhere('code', 'WS_CV');
        $this->assertTrue($row['effective_access']);

        // Global service disabled → not effective.
        WebService::query()->where('code', 'WS_CV')->update(['is_active' => false]);
        $row = collect($this->actingAs($user, 'api')->getJson('/api/v1/user/web-services')->json('data'))
            ->firstWhere('code', 'WS_CV');
        $this->assertFalse($row['effective_access']);
        WebService::query()->where('code', 'WS_CV')->update(['is_active' => true]);

        // Permission disabled → not effective.
        UserWebService::query()
            ->where('user_id', $user->id)
            ->where('web_service_id', WebService::query()->where('code', 'WS_CV')->value('id'))
            ->update(['is_enabled' => false]);
        $row = collect($this->actingAs($user, 'api')->getJson('/api/v1/user/web-services')->json('data'))
            ->firstWhere('code', 'WS_CV');
        $this->assertFalse($row['effective_access']);

        // User inactive → not effective (recomputed from the live DB state).
        $user->update(['is_active' => false]);
        $this->assertFalse($user->canConsumeWebService('WS_CV'));
    }
}
