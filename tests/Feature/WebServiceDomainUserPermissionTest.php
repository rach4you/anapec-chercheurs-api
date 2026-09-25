<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserDomain;
use App\Models\UserWebService;
use App\Models\WebService;
use App\Models\WebServiceDomain;
use Database\Factories\UserFactory;
use Database\Seeders\WebServiceDomainSeeder;
use Database\Seeders\WebServiceSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Phase 5 — User domain permission endpoints that the admin UI relies on.
 *
 * Covers the user-scoped domain grant endpoints:
 *   GET   /api/v1/admin/users/{id}/domains
 *   PUT   /api/v1/admin/users/{id}/domains
 *   PATCH /api/v1/admin/users/{id}/domains/{code}
 *
 * and verifies that domain grants actually change effective access while
 * leaving existing direct `api_user_web_services` permissions untouched.
 */
class WebServiceDomainUserPermissionTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = [null];

    protected function setUp(): void
    {
        parent::setUp();

        $this->beginDatabaseTransaction();
        $this->seed([
            WebServiceSeeder::class,
            WebServiceDomainSeeder::class,
        ]);
    }

    private function admin(): User
    {
        return UserFactory::new()->asAdmin()->create(['email' => 'wslui-admin@example.com', 'password' => 'secret123']);
    }

    private function user(string $email = 'wslui-user@example.com'): User
    {
        return UserFactory::new()->create(['email' => $email, 'password' => 'secret123']);
    }

    private function domain(string $code = 'CHERCHEURS'): WebServiceDomain
    {
        return WebServiceDomain::query()->where('code', $code)->firstOrFail();
    }

    // --- 1. Admin lists a user's domains (empty by default) ---
    public function test_admin_can_list_user_domains_empty(): void
    {
        $admin = $this->admin();
        $user = $this->user();

        $this->actingAs($admin, 'api')
            ->getJson("/api/v1/admin/users/{$user->id}/domains")
            ->assertOk()
            ->assertJsonPath('data.domains', []);
    }

    // --- 2. Admin assigns domains (full replace) ---
    public function test_admin_can_assign_user_domains(): void
    {
        $admin = $this->admin();
        $user = $this->user();

        $this->actingAs($admin, 'api')
            ->putJson("/api/v1/admin/users/{$user->id}/domains", ['domains' => ['CHERCHEURS']])
            ->assertOk();

        $this->assertTrue(
            UserDomain::query()
                ->where('user_id', $user->id)
                ->whereHas('domain', fn ($q) => $q->where('code', 'CHERCHEURS'))
                ->exists()
        );
    }

    // --- 3. Assigning replaces the previous set ---
    public function test_admin_assigning_user_domains_replaces_previous(): void
    {
        $admin = $this->admin();
        $user = $this->user();

        $altCode = 'T_'.strtoupper(substr(md5((string) mt_rand().uniqid()), 0, 8));
        WebServiceDomain::query()->create([
            'code' => $altCode,
            'name' => $altCode,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin, 'api')
            ->putJson("/api/v1/admin/users/{$user->id}/domains", ['domains' => ['CHERCHEURS']])
            ->assertOk();

        $this->actingAs($admin, 'api')
            ->putJson("/api/v1/admin/users/{$user->id}/domains", ['domains' => [$altCode]])
            ->assertOk();

        $codes = UserDomain::query()
            ->where('user_id', $user->id)
            ->whereHas('domain', fn ($q) => $q->whereIn('code', ['CHERCHEURS', $altCode]))
            ->with('domain')
            ->get()
            ->pluck('domain.code')
            ->all();

        $this->assertSame([$altCode], $codes);
    }

    // --- 4. Assigning an empty list is rejected ---
    public function test_assigning_empty_domain_list_is_rejected(): void
    {
        $admin = $this->admin();
        $user = $this->user();

        $this->actingAs($admin, 'api')
            ->putJson("/api/v1/admin/users/{$user->id}/domains", ['domains' => []])
            ->assertUnprocessable();
    }

    // --- 5. Assigning an unknown domain is rejected ---
    public function test_assigning_unknown_domain_is_rejected(): void
    {
        $admin = $this->admin();
        $user = $this->user();

        $this->actingAs($admin, 'api')
            ->putJson("/api/v1/admin/users/{$user->id}/domains", ['domains' => ['DOES_NOT_EXIST']])
            ->assertUnprocessable();
    }

    // --- 6. Admin toggles a single user domain grant ---
    public function test_admin_can_toggle_a_user_domain_grant(): void
    {
        $admin = $this->admin();
        $user = $this->user();
        $user->domains()->attach($this->domain()->id);

        $this->actingAs($admin, 'api')
            ->patchJson("/api/v1/admin/users/{$user->id}/domains/CHERCHEURS", ['is_enabled' => false])
            ->assertOk()
            ->assertJsonPath('data.is_enabled', false);

        $this->assertFalse(
            UserDomain::query()
                ->where('user_id', $user->id)
                ->whereHas('domain', fn ($q) => $q->where('code', 'CHERCHEURS'))
                ->first()->is_enabled
        );
    }

    // --- 7. Toggling an unknown domain returns 404 ---
    public function test_toggling_unknown_domain_returns_404(): void
    {
        $admin = $this->admin();
        $user = $this->user();

        $this->actingAs($admin, 'api')
            ->patchJson("/api/v1/admin/users/{$user->id}/domains/DOES_NOT_EXIST", ['is_enabled' => true])
            ->assertNotFound();
    }

    // --- 8. Regular user cannot manage their own domain grants ---
    public function test_regular_user_cannot_manage_user_domains(): void
    {
        $user = $this->user();

        $this->actingAs($user, 'api')
            ->getJson("/api/v1/admin/users/{$user->id}/domains")
            ->assertForbidden();

        $this->actingAs($user, 'api')
            ->putJson("/api/v1/admin/users/{$user->id}/domains", ['domains' => ['CHERCHEURS']])
            ->assertForbidden();

        $this->actingAs($user, 'api')
            ->patchJson("/api/v1/admin/users/{$user->id}/domains/CHERCHEURS", ['is_enabled' => true])
            ->assertForbidden();
    }

    // --- 9. Unauthenticated returns 401 ---
    public function test_unauthenticated_cannot_manage_user_domains(): void
    {
        $user = $this->user();
        $this->getJson("/api/v1/admin/users/{$user->id}/domains")->assertUnauthorized();
    }

    // --- 10. Domain grant drives effective access (no direct row needed) ---
    public function test_domain_grant_drives_effective_access(): void
    {
        $admin = $this->admin();
        $user = $this->user();

        // No direct permission yet.
        $this->assertFalse($user->canConsumeWebService('WS_CV'));

        $this->actingAs($admin, 'api')
            ->putJson("/api/v1/admin/users/{$user->id}/domains", ['domains' => ['CHERCHEURS']])
            ->assertOk();

        // Domain contains WS_CV → the user now has effective access.
        $this->assertTrue($user->fresh()->canConsumeWebService('WS_CV'));

        // Remove the domain → access is revoked without touching any
        // api_user_web_services row.
        $this->actingAs($admin, 'api')
            ->patchJson("/api/v1/admin/users/{$user->id}/domains/CHERCHEURS", ['is_enabled' => false])
            ->assertOk();

        $this->assertFalse($user->fresh()->canConsumeWebService('WS_CV'));

        // No direct permission row was ever created for WS_CV.
        $this->assertSame(
            0,
            UserWebService::query()
                ->where('user_id', $user->id)
                ->whereHas('webService', fn ($q) => $q->where('code', 'WS_CV'))
                ->count()
        );
    }

    // --- 11. Existing direct permissions are untouched by domain changes ---
    public function test_domain_changes_do_not_alter_direct_permissions(): void
    {
        $admin = $this->admin();
        $user = $this->user();

        $service = WebService::query()->where('code', 'WS_BILAN')->firstOrFail();
        $user->webServices()->attach($service->id);
        $this->assertTrue($user->canConsumeWebService('WS_BILAN'));

        $this->actingAs($admin, 'api')
            ->putJson("/api/v1/admin/users/{$user->id}/domains", ['domains' => ['CHERCHEURS']])
            ->assertOk();

        // The direct permission is preserved.
        $this->assertTrue($user->fresh()->canConsumeWebService('WS_BILAN'));
    }

    // --- 12. The user-scoped domains endpoint lists grants ---
    public function test_user_scoped_domains_endpoint_lists_grants(): void
    {
        $admin = $this->admin();
        $user = $this->user();

        $user->domains()->attach($this->domain()->id);

        $this->actingAs($admin, 'api')
            ->getJson("/api/v1/admin/users/{$user->id}/domains")
            ->assertOk()
            ->assertJsonCount(1, 'data.domains')
            ->assertJsonPath('data.domains.0.code', 'CHERCHEURS');
    }
}
