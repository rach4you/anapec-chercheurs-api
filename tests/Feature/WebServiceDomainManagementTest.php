<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WebService;
use App\Models\WebServiceDomain;
use Database\Factories\UserFactory;
use Database\Seeders\WebServiceDomainSeeder;
use Database\Seeders\WebServiceSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WebServiceDomainManagementTest extends TestCase
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

    private function domain(string $code): WebServiceDomain
    {
        return WebServiceDomain::query()->where('code', $code)->firstOrFail();
    }

    // Create a domain with a unique, test-specific code so each run is
    // deterministic even when the shared Oracle schema already holds other
    // rows. Returns the created domain.
    private function freshDomain(array $overrides = []): WebServiceDomain
    {
        $code = 'T_'.strtoupper(substr(md5((string) mt_rand().uniqid()), 0, 8));
        $domain = WebServiceDomain::query()->create(array_merge([
            'code' => $code,
            'name' => $code,
            'is_active' => true,
            'sort_order' => 0,
        ], $overrides));

        return $domain;
    }

    // --- 1. ADMIN can list domains ---
    public function test_admin_can_list_domains(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'domlist@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->getJson('/api/v1/admin/domains')
            ->assertOk()
            ->assertJsonPath('data.0.code', 'CHERCHEURS');
    }

    // --- 2. ADMIN can view a domain ---
    public function test_admin_can_view_a_domain(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'domview@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->getJson('/api/v1/admin/domains/CHERCHEURS')
            ->assertOk()
            ->assertJsonPath('data.code', 'CHERCHEURS')
            ->assertJsonCount(4, 'data.web_services');
    }

    // --- 3. Regular user cannot list domains ---
    public function test_regular_user_cannot_list_domains(): void
    {
        $user = UserFactory::new()->create(['email' => 'domdenied@example.com', 'password' => 'secret123']);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/admin/domains')
            ->assertForbidden();
    }

    // --- 4. Unauthenticated cannot list domains ---
    public function test_unauthenticated_cannot_list_domains(): void
    {
        $this->getJson('/api/v1/admin/domains')->assertUnauthorized();
    }

    // --- 5. ADMIN can create a domain ---
    public function test_admin_can_create_a_domain(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'domcreate@example.com', 'password' => 'secret123']);

        $code = 'T_'.strtoupper(substr(md5((string) mt_rand().uniqid()), 0, 8));

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/domains', [
                'code' => $code,
                'name' => 'Created',
                'description' => 'Created domain.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', $code)
            ->assertJsonPath('data.name', 'Created')
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.sort_order', 0);

        $this->assertNotNull(WebServiceDomain::query()->where('code', $code)->first());
    }

    // --- 6. Create domain with duplicate code is rejected ---
    public function test_create_domain_with_duplicate_code_is_rejected(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'domdup@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/domains', ['code' => 'chercheurs', 'name' => 'Duplicate'])
            ->assertUnprocessable();
    }

    // --- 7. Create domain requires name ---
    public function test_create_domain_requires_name(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'domnoname@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/domains', ['code' => 'NEW'])
            ->assertUnprocessable();
    }

    // --- 8. ADMIN can update a domain's metadata ---
    public function test_admin_can_update_a_domain(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'domupdate@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/domains/CHERCHEURS', [
                'name' => 'Chercheurs (updated)',
                'description' => 'Updated description.',
                'sort_order' => 5,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Chercheurs (updated)')
            ->assertJsonPath('data.sort_order', 5);

        $this->assertSame('Chercheurs (updated)', $this->domain('CHERCHEURS')->fresh()->name);
    }

    // --- 9. Domain code is immutable ---
    public function test_domain_code_is_immutable(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'domcode@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/domains/CHERCHEURS', ['code' => 'OTHER'])
            ->assertOk()
            ->assertJsonPath('data.code', 'CHERCHEURS');

        $this->assertSame('CHERCHEURS', $this->domain('CHERCHEURS')->fresh()->code);
    }

    // --- 10. Update on unknown domain returns 404 ---
    public function test_update_unknown_domain_returns_404(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'dom404@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/domains/DOES_NOT_EXIST', ['name' => 'X'])
            ->assertNotFound();
    }

    // --- 11. ADMIN can activate a domain ---
    public function test_admin_can_activate_a_domain(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'domact@example.com', 'password' => 'secret123']);
        $this->domain('CHERCHEURS')->update(['is_active' => false]);

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/domains/CHERCHEURS/status', ['is_active' => true])
            ->assertOk()
            ->assertJsonPath('data.code', 'CHERCHEURS')
            ->assertJsonPath('data.is_active', true);

        $this->assertTrue($this->domain('CHERCHEURS')->fresh()->is_active);
    }

    // --- 12. ADMIN can deactivate a domain ---
    public function test_admin_can_deactivate_a_domain(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'domdeact@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/domains/CHERCHEURS/status', ['is_active' => false])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertFalse($this->domain('CHERCHEURS')->fresh()->is_active);
    }

    // --- 13. Regular user cannot toggle domain status ---
    public function test_regular_user_cannot_toggle_domain_status(): void
    {
        $user = UserFactory::new()->create(['email' => 'domststatus@example.com', 'password' => 'secret123']);

        $this->actingAs($user, 'api')
            ->patchJson('/api/v1/admin/domains/CHERCHEURS/status', ['is_active' => false])
            ->assertForbidden();
    }

    // --- 14. Toggle status requires a boolean ---
    public function test_toggle_status_rejects_missing_value(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'dombool@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/domains/CHERCHEURS/status', [])
            ->assertUnprocessable();
    }

    // --- 3. Assign is a full replacement ---
    public function test_admin_can_assign_services_to_a_domain_replaces_memberships(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'domreplace@example.com', 'password' => 'secret123']);
        $domain = $this->freshDomain(['name' => 'Replace']);

        $this->actingAs($admin, 'api')
            ->putJson('/api/v1/admin/domains/'.$domain->code.'/services', [
                'web_services' => ['WS_CHECK_CIN'],
            ])
            ->assertOk()
            ->assertJsonCount(1, 'data.web_services');

        $this->actingAs($admin, 'api')
            ->putJson('/api/v1/admin/domains/'.$domain->code.'/services', [
                'web_services' => ['WS_CV'],
            ])
            ->assertOk()
            ->assertJsonCount(1, 'data.web_services');

        $this->assertSame(['WS_CV'], $domain->webServices()->pluck('code')->all());
    }

    // --- 16. Assigning an empty list is rejected ---
    public function test_assigning_empty_service_list_is_rejected(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'domempty@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->putJson('/api/v1/admin/domains/CHERCHEURS/services', ['web_services' => []])
            ->assertUnprocessable();
    }

    // --- 17. Assigning an unknown service is rejected ---
    public function test_assigning_unknown_service_is_rejected(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'domunknown@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->putJson('/api/v1/admin/domains/CHERCHEURS/services', ['web_services' => ['WS_NOPE']])
            ->assertUnprocessable();

        $this->assertSame(4, $this->domain('CHERCHEURS')->webServices()->count());
    }

    // --- 18. Duplicate service codes are deduped ---
    public function test_duplicate_service_codes_are_deduped(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'domdedupe@example.com', 'password' => 'secret123']);

        $this->actingAs($admin, 'api')
            ->putJson('/api/v1/admin/domains/CHERCHEURS/services', [
                'web_services' => ['WS_CV', 'WS_CV', 'ws_cv'],
            ])
            ->assertOk()
            ->assertJsonCount(1, 'data.web_services');

        $this->assertSame(1, $this->domain('CHERCHEURS')->webServices()->count());
    }

    // --- 19. Regular user cannot assign services to a domain ---
    public function test_regular_user_cannot_assign_domain_services(): void
    {
        $user = UserFactory::new()->create(['email' => 'domassign@example.com', 'password' => 'secret123']);

        $this->actingAs($user, 'api')
            ->putJson('/api/v1/admin/domains/CHERCHEURS/services', ['web_services' => ['WS_CV']])
            ->assertForbidden();
    }

    // --- 20. Domain does not alter existing user permissions (backward compat) ---
    public function test_domain_does_not_alter_existing_user_permissions(): void
    {
        $admin = UserFactory::new()->asAdmin()->create(['email' => 'dombackcompat@example.com', 'password' => 'secret123']);
        $regular = UserFactory::new()->create(['email' => 'dombackcompatuser@example.com', 'password' => 'secret123']);

        $service = WebService::query()->where('code', 'WS_CV')->firstOrFail();
        $regular->webServices()->attach($service->id);

        $this->actingAs($admin, 'api')
            ->putJson('/api/v1/admin/domains/CHERCHEURS/services', ['web_services' => ['WS_CV']])
            ->assertOk();

        // Existing direct permission is untouched by the domain change.
        $this->assertTrue($regular->canConsumeWebService('WS_CV'));
    }
}
