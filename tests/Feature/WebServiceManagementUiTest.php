<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WebService;
use Database\Factories\UserFactory;
use Database\Seeders\WebServiceSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WebServiceManagementUiTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = [null];

    protected function setUp(): void
    {
        parent::setUp();

        $this->beginDatabaseTransaction();
        $this->seed(WebServiceSeeder::class);
    }

    private function ws(string $code): WebService
    {
        return WebService::query()->where('code', $code)->firstOrFail();
    }

    // Admin endpoint (reused by the Phase 3.3 UI) returns 200 + the full list.
    public function test_admin_can_list_web_services_for_ui(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'ws-ui-admin@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->getJson('/api/v1/admin/web-services')
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('data.0.code', 'WS_BILAN');
    }

    // A regular user must be denied access to the admin listing endpoint.
    public function test_regular_user_cannot_list_web_services(): void
    {
        $user = UserFactory::new()->create(['email' => 'ws-ui-user@example.com', 'password' => 'secret123']);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/admin/web-services')
            ->assertStatus(403);
    }

    // A regular user cannot change the global status (the backend layer
    // that the frontend confirmation step relies on).
    public function test_regular_user_cannot_toggle_global_status(): void
    {
        $user = UserFactory::new()->create(['email' => 'ws-ui-toggle@example.com', 'password' => 'secret123']);

        $this->actingAs($user, 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV/status', ['is_active' => false])
            ->assertStatus(403);

        $this->assertTrue($this->ws('WS_CV')->fresh()->is_active, 'Global status must remain unchanged for a regular user');
    }

    // Admin can activate a service globally.
    public function test_admin_can_activate_a_service(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'ws-ui-on@example.com', 'password' => 'secret123']);
        $ws = $this->ws('WS_PROFILE');
        $ws->update(['is_active' => false]);

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/web-services/WS_PROFILE/status', ['is_active' => true])
            ->assertOk()
            ->assertJsonPath('data.code', 'WS_PROFILE')
            ->assertJsonPath('data.is_active', true);

        $this->assertTrue($ws->fresh()->is_active);
    }

    // Admin can deactivate a service globally.
    public function test_admin_can_deactivate_a_service(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'ws-ui-off@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV/status', ['is_active' => false])
            ->assertOk()
            ->assertJsonPath('data.code', 'WS_CV')
            ->assertJsonPath('data.is_active', false);

        $this->assertFalse($this->ws('WS_CV')->fresh()->is_active);
    }

    // The toggle endpoint validates the payload: a missing / null is_active is rejected.
    // (Strings are intentionally coerced by prepareForValidation, so only absence fails.)
    public function test_toggle_requires_is_active_present(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'ws-ui-val@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV/status', [])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['is_active']]);
    }

    // A null is_active is also rejected.
    public function test_toggle_rejects_null_is_active(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'ws-ui-val2@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV/status', ['is_active' => null])
            ->assertStatus(422);
    }

    // Validation: a string "true" is coerced and accepted (prepareForValidation).
    public function test_toggle_accepts_string_boolean(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'ws-ui-str@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV/status', ['is_active' => 'false'])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertFalse($this->ws('WS_CV')->fresh()->is_active);
    }
}
