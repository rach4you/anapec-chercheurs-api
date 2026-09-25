<?php

namespace Tests\Feature;

use App\Models\DomainWebService;
use App\Models\User;
use App\Models\WebService;
use App\Models\WebServiceDomain;
use Database\Factories\UserFactory;
use Database\Seeders\WebServiceDomainSeeder;
use Database\Seeders\WebServiceSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Phase 5/6 — UX part D.
 *
 * Verifies that the existing Phase 4 admin API endpoints expose each Web
 * Service's domain membership through a `domains` field, and that the admin
 * list / detail views render the matching "Domaines" markers. No
 * authorization semantics, effective-access logic, or database schema is
 * changed by this test.
 */
class WebServiceDomainMembershipUiTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = [null];

    protected function setUp(): void
    {
        parent::setUp();
        $this->beginDatabaseTransaction();
        $this->seed(WebServiceSeeder::class);
        $this->seed(WebServiceDomainSeeder::class);
    }

    private function admin(): User
    {
        return UserFactory::new()->asAdmin()->create([
            'email' => 'wsmembertest@example.com',
            'password' => 'secret123',
        ]);
    }

    // GET /api/v1/admin/web-services returns each service with a `domains` array.
    public function test_index_exposes_domains_array_on_every_service(): void
    {
        $response = $this->actingAs($this->admin(), 'api')
            ->getJson('/api/v1/admin/web-services')
            ->assertOk();

        $services = $response->json('data');
        $this->assertIsArray($services);
        $this->assertCount(4, $services);

        foreach ($services as $service) {
            $this->assertArrayHasKey('domains', $service, 'Every service must expose a `domains` field.');
            $this->assertIsArray($service['domains']);
        }
    }

    // A service attached to the seeded domain lists that domain code.
    public function test_index_service_in_seeded_domain_lists_its_domain(): void
    {
        $response = $this->actingAs($this->admin(), 'api')
            ->getJson('/api/v1/admin/web-services')
            ->assertOk();

        $ws = $response->json('data');
        $checkCin = collect($ws)->firstWhere('code', 'WS_CHECK_CIN');

        $this->assertNotNull($checkCin, 'WS_CHECK_CIN must be present in the list.');
        $codes = collect($checkCin['domains'])->pluck('code')->all();
        $this->assertContains('CHERCHEURS', $codes);
    }

    // A service that is not in any domain exposes an empty `domains` array.
    public function test_index_service_without_domains_lists_empty_array(): void
    {
        $domainId = WebServiceDomain::query()->where('code', 'CHERCHEURS')->firstOrFail()->id;
        $wsId = WebService::query()->where('code', 'WS_BILAN')->firstOrFail()->id;

        // Detach the service from every domain so it belongs to none.
        DomainWebService::query()
            ->where('web_service_id', $wsId)
            ->where('domain_id', $domainId)
            ->delete();

        $response = $this->actingAs($this->admin(), 'api')
            ->getJson('/api/v1/admin/web-services')
            ->assertOk();

        $ws = collect($response->json('data'))->firstWhere('code', 'WS_BILAN');
        $this->assertNotNull($ws, 'WS_BILAN must be present in the list.');
        $this->assertSame([], $ws['domains']);
    }

    // GET /api/v1/admin/web-services/{code} returns the same `domains` field.
    public function test_show_exposes_the_same_domains_field(): void
    {
        $response = $this->actingAs($this->admin(), 'api')
            ->getJson('/api/v1/admin/web-services/WS_CV')
            ->assertOk();

        $service = $response->json('data');
        $this->assertIsArray($service['domains']);

        $codes = collect($service['domains'])->pluck('code')->all();
        $this->assertContains('CHERCHEURS', $codes);
    }

    // The admin list view renders and contains the "Domaines" column marker.
    public function test_list_view_renders_with_domaines_marker(): void
    {
        $this->get('/admin/web-services')
            ->assertOk()
            ->assertSee('admin-web-services-root')
            ->assertSee('Domaines');
    }

    // The admin detail view renders and contains the "Domaines" section marker.
    public function test_detail_view_renders_with_domaines_marker(): void
    {
        $this->get('/admin/web-services/WS_CV')
            ->assertOk()
            ->assertSee('admin-ws-show-root')
            ->assertSee('detail-domains')
            ->assertSee('Domaines');
    }
}
