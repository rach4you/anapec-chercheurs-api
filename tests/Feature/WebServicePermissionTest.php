<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserCanConsumeWebService;
use App\Models\User;
use App\Models\WebService;
use Database\Factories\UserFactory;
use Database\Seeders\WebServiceSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class WebServicePermissionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Connections that should be wrapped in a transaction.
     *
     * @var list<string|null>
     */
    protected $connectionsToTransact = [null];

    protected function setUp(): void
    {
        parent::setUp();

        // The schema (personal_access_tokens, api_users, api_web_services,
        // api_user_web_services) is already migrated in the live Oracle DB.
        // Wrap each test in a transaction that is rolled back afterwards,
        // so no persistent data is left behind and the shared SIGEC schema
        // is never dropped.
        $this->beginDatabaseTransaction();

        // Ensure the four canonical Web Services exist in the live Oracle
        // schema. The seeder is idempotent (firstOrCreate), so it is safe to
        // run even when the real DB already has them from earlier runs.
        $this->seed(WebServiceSeeder::class);
    }

    /**
     * Resolve a Web Service by its canonical code.
     */
    private function ws(string $code): WebService
    {
        return WebService::query()->where('code', $code)->firstOrFail();
    }

    /**
     * Invoke EnsureUserCanConsumeWebService for the given user + WS code.
     *
     * @return string 'PASS' or the 403 denial message
     */
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

    public function test_a_user_can_log_in_and_read_their_own_profile(): void
    {
        $user = UserFactory::new()->create(['email' => 'jane@example.com', 'password' => 'secret123']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'token', 'token_type'],
            ]);

        $this->assertNotEmpty($response->json('data.token'));

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'jane@example.com');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        UserFactory::new()->create(['email' => 'nope@example.com', 'password' => 'secret123']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'nope@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(401);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        UserFactory::new()->asInactive()->create(['email' => 'off@example.com', 'password' => 'secret123']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'off@example.com',
            'password' => 'secret123',
        ])->assertStatus(403);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $user = UserFactory::new()->create(['email' => 'plain@example.com', 'password' => 'secret123']);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/admin/users')
            ->assertStatus(403);
    }

    public function test_admin_can_assign_web_services_to_a_user(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'admin@example.com', 'password' => 'secret123']);
        $target = UserFactory::new()->create(['email' => 'target@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->putJson('/api/v1/admin/users/'.$target->id.'/web-services', [
                'web_services' => [
                    ['code' => 'WS_CHECK_CIN', 'enabled' => true],
                    ['code' => 'WS_PROFILE', 'enabled' => false],
                ],
            ])
            ->assertOk();

        $fresh = $target->fresh();
        $this->assertTrue($fresh->canConsumeWebService('WS_CHECK_CIN'));
        $this->assertFalse($fresh->canConsumeWebService('WS_PROFILE'));

        $this->actingAs($admin, 'api')
            ->getJson('/api/v1/admin/users/'.$target->id.'/web-services')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_gate_denies_user_without_permission(): void
    {
        $user = UserFactory::new()->create(['email' => 'locked@example.com', 'password' => 'secret123']);

        $this->assertSame('Insufficient permissions.', $this->runGate($user, 'WS_CV'));
    }

    public function test_gate_allows_user_with_explicit_permission(): void
    {
        $user = UserFactory::new()->create(['email' => 'member@example.com', 'password' => 'secret123']);
        $user->webServices()->attach($this->ws('WS_BILAN')->id, ['is_enabled' => true]);

        $this->assertSame('PASS', $this->runGate($user, 'WS_BILAN'));
    }

    public function test_gate_denies_inactive_web_service_even_with_permission(): void
    {
        $ws = $this->ws('WS_BILAN');

        // Temporarily disable an existing canonical Web Service within the
        // transaction; the rollback restores the real row afterwards.
        $ws->update(['is_active' => false]);

        $user = UserFactory::new()->create(['email' => 'x@example.com', 'password' => 'secret123']);
        $user->webServices()->attach($ws->id, ['is_enabled' => true]);

        $this->assertSame('Web Service is disabled.', $this->runGate($user, 'WS_BILAN'));
    }

    public function test_gate_denies_inactive_user(): void
    {
        $user = UserFactory::new()->asInactive()->create(['email' => 'zzz@example.com', 'password' => 'secret123']);
        $user->webServices()->attach($this->ws('WS_CV')->id, ['is_enabled' => true]);

        $this->assertSame('Account is deactivated.', $this->runGate($user, 'WS_CV'));
    }

    public function test_unauthenticated_me_returns_401(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401);
    }

    public function test_authenticated_me_returns_profile(): void
    {
        $user = UserFactory::new()->create(['email' => 'profile@example.com', 'password' => 'secret123']);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'profile@example.com')
            ->assertJsonPath('data.role', 'user');
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = UserFactory::new()->create(['email' => 'logout@example.com', 'password' => 'secret123']);

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_admin_can_access_admin_user_index(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'boss@example.com', 'password' => 'secret123']);
        $plain = UserFactory::new()->create(['email' => 'plain2@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->getJson('/api/v1/admin/users')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->assertNotNull($plain);
    }

    public function test_admin_can_create_a_user(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'boss2@example.com', 'password' => 'secret123']);

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'secret123',
                'role' => 'user',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.email', 'newuser@example.com')
            ->assertJsonPath('data.role', 'user');

        $this->assertDatabaseHas('api_users', ['email' => 'newuser@example.com']);
    }

    public function test_admin_can_deactivate_a_user(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'boss3@example.com', 'password' => 'secret123']);
        $target = UserFactory::new()->create(['email' => 'victim@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/users/'.$target->id.'/status', ['is_active' => false])
            ->assertOk();

        $this->assertFalse($target->fresh()->is_active);
    }

    public function test_admin_can_reactivate_a_user(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'boss4@example.com', 'password' => 'secret123']);
        $target = UserFactory::new()->asInactive()->create(['email' => 'revive@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/users/'.$target->id.'/status', ['is_active' => true])
            ->assertOk();

        $this->assertTrue($target->fresh()->is_active);
    }

    public function test_user_cannot_change_own_role_via_admin_endpoint(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'boss5@example.com', 'password' => 'secret123']);
        $user = UserFactory::new()->create(['email' => 'pleb@example.com', 'password' => 'secret123']);

        // Non-admin user cannot reach admin endpoints at all
        $this->actingAs($user, 'api')
            ->putJson('/api/v1/admin/users/'.$user->id, ['role' => 'admin'])
            ->assertStatus(403);
    }

    public function test_user_cannot_modify_permissions(): void
    {
        $user = UserFactory::new()->create(['email' => 'permuser@example.com', 'password' => 'secret123']);

        $this->actingAs($user, 'api')
            ->putJson('/api/v1/admin/users/'.$user->id.'/web-services', [
                'web_services' => [['code' => 'WS_CV', 'enabled' => true]],
            ])
            ->assertStatus(403);
    }

    public function test_password_never_appears_in_api_response(): void
    {
        $user = UserFactory::new()->create(['email' => 'leak@example.com', 'password' => 'secret123']);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonMissingPath('data.password');

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/auth/me')
            ->assertOk();

        $body = $response->getContent();
        $this->assertStringNotContainsString('secret123', $body);
    }

    public function test_unknown_user_login_returns_401(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'ghost@example.com',
            'password' => 'secret123',
        ])->assertStatus(401);
    }
}
