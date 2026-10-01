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

class WebServiceCreateTest extends TestCase
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
        return UserFactory::new()->asAdmin()->create(['email' => 'ws-create-admin@example.com', 'password' => 'secret123']);
    }

    private function user(): User
    {
        return UserFactory::new()->create(['email' => 'ws-create-user@example.com', 'password' => 'secret123']);
    }

    // Unique code so runs are deterministic against the shared Oracle schema.
    private function freshCode(string $prefix = 'WS'): string
    {
        return $prefix.'_'.strtoupper(substr(md5((string) mt_rand().uniqid()), 0, 10));
    }

    private function freshDomain(array $overrides = []): WebServiceDomain
    {
        $code = 'T_'.strtoupper(substr(md5((string) mt_rand().uniqid()), 0, 8));
        return WebServiceDomain::query()->create(array_merge([
            'code' => $code,
            'name' => $code,
            'is_active' => true,
            'sort_order' => 0,
        ], $overrides));
    }

    // --- 1. Admin can create a Web Service ---
    public function test_admin_can_create_a_web_service(): void
    {
        $admin = $this->admin();
        $code = $this->freshCode();

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => $code,
                'name' => 'Nouveau Service',
                'description' => 'Description du nouveau service.',
                'is_active' => true,
            ])
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Web Service created.')
            ->assertJsonPath('data.code', $code)
            ->assertJsonPath('data.name', 'Nouveau Service')
            ->assertJsonPath('data.description', 'Description du nouveau service.')
            ->assertJsonPath('data.is_active', true);

        $this->assertNotNull(WebService::query()->where('code', $code)->first());
    }

    // --- 2. Code is required ---
    public function test_create_requires_code(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'name' => 'Sans code',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }

    // --- 3. Code must be unique ---
    public function test_create_rejects_duplicate_code(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => 'WS_BILAN',
                'name' => 'Dupliqué',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }

    // --- 4. Name is required ---
    public function test_create_requires_name(): void
    {
        $admin = $this->admin();
        $code = $this->freshCode();

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => $code,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    // --- 5. Selected Domain must exist ---
    public function test_create_rejects_unknown_domain_code(): void
    {
        $admin = $this->admin();
        $code = $this->freshCode();

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => $code,
                'name' => 'With bad domain',
                'domain_codes' => ['NOPE_DOES_NOT_EXIST'],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('domain_codes.0');
    }

    // --- 6. Created service is active when requested ---
    public function test_created_service_is_active_when_requested(): void
    {
        $admin = $this->admin();
        $code = $this->freshCode();

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => $code,
                'name' => 'Active',
                'is_active' => true,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.is_active', true);

        $this->assertTrue(WebService::query()->where('code', $code)->firstOrFail()->is_active);
    }

    public function test_created_service_defaults_to_active_when_omitted(): void
    {
        $admin = $this->admin();
        $code = $this->freshCode();

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => $code,
                'name' => 'Default Active',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.is_active', true);

        $this->assertTrue(WebService::query()->where('code', $code)->firstOrFail()->is_active);
    }

    public function test_created_service_can_be_inactive(): void
    {
        $admin = $this->admin();
        $code = $this->freshCode();

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => $code,
                'name' => 'Inactive',
                'is_active' => false,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.is_active', false);

        $this->assertFalse(WebService::query()->where('code', $code)->firstOrFail()->is_active);
    }

    // --- 7. Created service is returned correctly ---
    public function test_create_returns_web_service_resource_shape(): void
    {
        $admin = $this->admin();
        $code = $this->freshCode();

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => $code,
                'name' => 'Resource Shape',
                'description' => 'desc',
            ]);

        $response->assertStatus(201);

        $data = $response->json('data');
        foreach (['id', 'code', 'name', 'description', 'is_active', 'domains', 'created_at', 'updated_at'] as $field) {
            $this->assertArrayHasKey($field, $data, 'Response data must include "'.$field.'".');
        }
        $this->assertSame($code, $data['code']);
        $this->assertIsArray($data['domains']);
    }

    // --- 8. Domain relationship is created correctly ---
    public function test_create_attaches_existing_domain(): void
    {
        $admin = $this->admin();
        $code = $this->freshCode();

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => $code,
                'name' => 'With Domain',
                'domain_codes' => ['CHERCHEURS'],
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.domains.0.code', 'CHERCHEURS');

        $service = WebService::query()->with('domains')->where('code', $code)->firstOrFail();
        $this->assertTrue($service->domains->contains('code', 'CHERCHEURS'));
    }

    public function test_create_attaches_multiple_domains(): void
    {
        $admin = $this->admin();
        $code = $this->freshCode();
        $extra = $this->freshDomain(['code' => 'TEST_EXTRA_D', 'name' => 'Extra Domain']);

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => $code,
                'name' => 'Two Domains',
                'domain_codes' => ['CHERCHEURS', $extra->code],
            ])
            ->assertStatus(201)
            ->assertJsonCount(2, 'data.domains');

        $service = WebService::query()->where('code', $code)->firstOrFail();
        $this->assertSame(2, $service->domains()->count());
    }

    public function test_create_with_empty_domain_list_is_allowed(): void
    {
        $admin = $this->admin();
        $code = $this->freshCode();

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => $code,
                'name' => 'No Domain',
                'domain_codes' => [],
            ])
            ->assertStatus(201)
            ->assertJsonCount(0, 'data.domains');

        $service = WebService::query()->where('code', $code)->firstOrFail();
        $this->assertSame(0, $service->domains()->count());
    }

    public function test_create_does_not_create_a_new_domain(): void
    {
        $admin = $this->admin();
        $code = $this->freshCode();

        $before = WebServiceDomain::query()->count();

        // Even though the payload references an existing domain code, no new
        // domain row may be created.
        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => $code,
                'name' => 'No New Domain',
                'domain_codes' => ['CHERCHEURS'],
            ])
            ->assertStatus(201);

        $this->assertSame($before, WebServiceDomain::query()->count());
    }

    public function test_code_is_normalized_to_uppercase(): void
    {
        $admin = $this->admin();
        $code = $this->freshCode();
        $lower = strtolower($code);

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => $lower,
                'name' => 'Uppercase Normalized',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.code', $code);

        $this->assertNotNull(WebService::query()->where('code', $code)->first());
        $this->assertNull(WebService::query()->where('code', $lower)->first());
    }

    // --- 9. Non-admin cannot create a Web Service ---
    public function test_regular_user_cannot_create_web_service(): void
    {
        $user = $this->user();
        $code = $this->freshCode();

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => $code,
                'name' => 'Hacked',
            ])
            ->assertStatus(403);

        $this->assertNull(WebService::query()->where('code', $code)->first());
    }

    public function test_unauthenticated_cannot_create_web_service(): void
    {
        $code = $this->freshCode();

        $this->postJson('/api/v1/admin/web-services', [
            'code' => $code,
            'name' => 'Unauth',
        ])->assertStatus(401);

        $this->assertNull(WebService::query()->where('code', $code)->first());
    }

    // --- 10. Existing update endpoint still works ---
    public function test_existing_update_endpoint_still_works(): void
    {
        $admin = $this->admin();
        $code = $this->freshCode();
        WebService::query()->create([
            'code' => $code,
            'name' => 'Original',
            'description' => 'orig',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/web-services/'.$code, [
                'name' => 'Updated',
                'description' => 'upd',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated');
    }

    // --- 11. Existing toggle status still works ---
    public function test_existing_toggle_status_still_works(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV/status', ['is_active' => false])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);
    }

    // --- 12. Existing Web Services list still works ---
    public function test_existing_list_endpoint_still_works(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'api')
            ->getJson('/api/v1/admin/web-services')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    // --- 13. Existing Web Service details still work ---
    public function test_existing_show_endpoint_still_works(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'api')
            ->getJson('/api/v1/admin/web-services/WS_CV')
            ->assertOk()
            ->assertJsonPath('data.code', 'WS_CV');
    }

    // --- UI: create modal renders on the list view ---
    public function test_list_view_renders_create_modal_and_button(): void
    {
        $this->get('/admin/web-services')
            ->assertOk()
            ->assertSee('btn-create-ws')
            ->assertSee('ws-create-modal')
            ->assertSee('Créer un Web Service')
            ->assertSee('create-ws-code')
            ->assertSee('create-ws-name')
            ->assertSee('create-ws-description')
            ->assertSee('create-ws-domains-list')
            ->assertSee('create-ws-is-active')
            ->assertSee('btn-create-ws-submit');
    }

    public function test_list_view_keeps_existing_identifiers_intact(): void
    {
        $this->get('/admin/web-services')
            ->assertOk()
            ->assertSee('admin-web-services-root')
            ->assertSee('ws-tbody')
            ->assertSee('ws-table')
            ->assertSee('Domaines')
            ->assertSee('edit-ws-modal')
            ->assertSee('status-modal');
    }

    public function test_list_view_uses_metronic_layout(): void
    {
        $this->get('/admin/web-services')
            ->assertOk()
            ->assertSee('kt_app_sidebar')
            ->assertSee('kt_app_header')
            ->assertSee('kt_app_content_container')
            ->assertSee('style.bundle.css');
    }

    // --- Validation edge cases ---
    public function test_code_length_limit_is_enforced(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => str_repeat('A', 61),
                'name' => 'Long code',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }

    public function test_code_rejects_special_characters(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => 'WS WITH SPACES!',
                'name' => 'Bad code',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }

    public function test_name_length_limit_is_enforced(): void
    {
        $admin = $this->admin();
        $code = $this->freshCode();

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => $code,
                'name' => str_repeat('a', 256),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_description_length_limit_is_enforced(): void
    {
        $admin = $this->admin();
        $code = $this->freshCode();

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => $code,
                'name' => 'Name',
                'description' => str_repeat('a', 501),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('description');
    }

    public function test_domain_codes_must_be_distinct(): void
    {
        $admin = $this->admin();
        $code = $this->freshCode();

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => $code,
                'name' => 'Dup domain',
                'domain_codes' => ['CHERCHEURS', 'CHERCHEURS'],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['domain_codes.0', 'domain_codes.1']);
    }

    public function test_code_is_trimmed_before_persistence(): void
    {
        $admin = $this->admin();
        $code = $this->freshCode();

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/admin/web-services', [
                'code' => '  '.strtolower($code).'  ',
                'name' => 'Trimmed',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.code', $code);

        $this->assertNotNull(WebService::query()->where('code', $code)->first());
    }
}
