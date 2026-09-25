<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserCanConsumeWebService;
use App\Models\User;
use App\Models\UserWebService;
use App\Models\WebService;
use Database\Factories\UserFactory;
use Database\Seeders\WebServiceSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class WebServiceManagementTest extends TestCase
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

    private function runGate(User $user, string $code): string
    {
        $middleware = new EnsureUserCanConsumeWebService;
        $request = Request::create('/api/v1/__ws/'.$code, 'GET');
        $request->setUserResolver(fn () => $user);
        $response = $middleware->handle($request, fn () => 'PASS', $code);

        if ($response === 'PASS') {
            return 'PASS';
        }

        return $response->getData(true)['message'] ?? 'UNKNOWN';
    }

    // --- 1. ADMIN can list Web Services ---
    public function test_admin_can_list_web_services(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'wslist@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->getJson('/api/v1/admin/web-services')
            ->assertOk()
            ->assertJsonCount(4, 'data');
    }

    // --- 2. ADMIN can view a Web Service ---
    public function test_admin_can_view_a_web_service(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'wsview@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->getJson('/api/v1/admin/web-services/WS_CV')
            ->assertOk()
            ->assertJsonPath('data.code', 'WS_CV');
    }

    // --- 3. ADMIN can activate a Web Service ---
    public function test_admin_can_activate_a_web_service(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'wsact@example.com', 'password' => 'secret123']);
        $ws = $this->ws('WS_CV');
        $ws->update(['is_active' => false]);

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV/status', ['is_active' => true])
            ->assertOk()
            ->assertJsonPath('data.code', 'WS_CV')
            ->assertJsonPath('data.is_active', true);

        $this->assertTrue($ws->fresh()->is_active);
    }

    // --- 4. ADMIN can deactivate a Web Service ---
    public function test_admin_can_deactivate_a_web_service(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'wsdeact@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV/status', ['is_active' => false])
            ->assertOk()
            ->assertJsonPath('data.code', 'WS_CV')
            ->assertJsonPath('data.is_active', false);

        $this->assertFalse($this->ws('WS_CV')->fresh()->is_active);
    }

    // --- 5. USER cannot modify global Web Service status ---
    public function test_user_cannot_modify_global_web_service_status(): void
    {
        $user = UserFactory::new()->create(['email' => 'wsuser@example.com', 'password' => 'secret123']);

        $this->actingAs($user, 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV/status', ['is_active' => false])
            ->assertStatus(403);
    }

    // --- 6. ADMIN can assign a Web Service to a user ---
    public function test_admin_can_assign_web_service_to_user(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'wsassign@example.com', 'password' => 'secret123']);
        $target = UserFactory::new()->create(['email' => 'wstarget@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->putJson('/api/v1/admin/users/'.$target->id.'/web-services', [
                'web_services' => [['code' => 'WS_CV', 'enabled' => true]],
            ])
            ->assertOk();

        $this->assertTrue($target->fresh()->canConsumeWebService('WS_CV'));
    }

    // --- 7. ADMIN can disable a user's Web Service permission ---
    public function test_admin_can_disable_user_web_service_permission(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'wsdis@example.com', 'password' => 'secret123']);
        $target = UserFactory::new()->create(['email' => 'wsdis2@example.com', 'password' => 'secret123']);

        $target->webServices()->attach($this->ws('WS_CV')->id, ['is_enabled' => true]);
        $this->assertTrue($target->fresh()->canConsumeWebService('WS_CV'));

        $this->actingAs($admin, 'api')
            ->putJson('/api/v1/admin/users/'.$target->id.'/web-services', [
                'web_services' => [['code' => 'WS_CV', 'enabled' => false]],
            ])
            ->assertOk();

        $this->assertFalse($target->fresh()->canConsumeWebService('WS_CV'));
    }

    // --- 8. USER cannot modify permissions ---
    public function test_user_cannot_modify_permissions(): void
    {
        $user = UserFactory::new()->create(['email' => 'wsperm@example.com', 'password' => 'secret123']);

        $this->actingAs($user, 'api')
            ->putJson('/api/v1/admin/users/'.$user->id.'/web-services', [
                'web_services' => [['code' => 'WS_CV', 'enabled' => true]],
            ])
            ->assertStatus(403);
    }

    // --- 9. User with enabled permission + global ON can consume ---
    public function test_enabled_permission_with_global_on_passes(): void
    {
        $user = UserFactory::new()->create(['email' => 'wsok@example.com', 'password' => 'secret123']);
        $user->webServices()->attach($this->ws('WS_CV')->id, ['is_enabled' => true]);

        $this->assertSame('PASS', $this->runGate($user, 'WS_CV'));
    }

    // --- 10. User with enabled permission + global OFF receives 403 ---
    public function test_enabled_permission_with_global_off_receives_403(): void
    {
        $user = UserFactory::new()->create(['email' => 'ws403a@example.com', 'password' => 'secret123']);
        $user->webServices()->attach($this->ws('WS_CV')->id, ['is_enabled' => true]);
        $this->ws('WS_CV')->update(['is_active' => false]);

        $this->assertSame('Web Service is disabled.', $this->runGate($user, 'WS_CV'));
    }

    // --- 11. User with disabled permission + global ON receives 403 ---
    public function test_disabled_permission_with_global_on_receives_403(): void
    {
        $user = UserFactory::new()->create(['email' => 'ws403b@example.com', 'password' => 'secret123']);
        $user->webServices()->attach($this->ws('WS_CV')->id, ['is_enabled' => false]);

        $this->assertSame('Insufficient permissions.', $this->runGate($user, 'WS_CV'));
    }

    // --- 12. Inactive user receives 403 ---
    public function test_inactive_user_receives_403(): void
    {
        $user = UserFactory::new()->asInactive()->create(['email' => 'wsinactive@example.com', 'password' => 'secret123']);
        $user->webServices()->attach($this->ws('WS_CV')->id, ['is_enabled' => true]);

        $this->assertSame('Account is deactivated.', $this->runGate($user, 'WS_CV'));
    }

    // --- 13. Nonexistent Web Service receives appropriate response ---
    public function test_nonexistent_web_service_receives_403(): void
    {
        $user = UserFactory::new()->create(['email' => 'wsunknown@example.com', 'password' => 'secret123']);

        $this->assertSame('Unknown Web Service.', $this->runGate($user, 'WS_DOES_NOT_EXIST'));
    }

    // --- 14. Global OFF does not delete user permissions ---
    public function test_global_off_does_not_delete_user_permissions(): void
    {
        $user = UserFactory::new()->create(['email' => 'wskeep@example.com', 'password' => 'secret123']);
        $user->webServices()->attach($this->ws('WS_CV')->id, ['is_enabled' => true]);

        $ws = $this->ws('WS_CV');
        $ws->update(['is_active' => false]);

        $pivot = UserWebService::query()
            ->where('user_id', $user->id)
            ->where('web_service_id', $ws->id)
            ->first();

        $this->assertNotNull($pivot, 'Permission record should still exist after global OFF');
        $this->assertTrue($pivot->is_enabled, 'Permission should remain enabled after global OFF');
    }

    // --- 15. Turning global ON again restores access ---
    public function test_turning_global_on_again_restores_access(): void
    {
        $user = UserFactory::new()->create(['email' => 'wsrestore@example.com', 'password' => 'secret123']);
        $user->webServices()->attach($this->ws('WS_CV')->id, ['is_enabled' => true]);

        $ws = $this->ws('WS_CV');
        $ws->update(['is_active' => false]);
        $this->assertSame('Web Service is disabled.', $this->runGate($user, 'WS_CV'));

        $ws->update(['is_active' => true]);
        $this->assertSame('PASS', $this->runGate($user, 'WS_CV'));
    }
}
