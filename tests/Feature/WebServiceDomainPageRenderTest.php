<?php

namespace Tests\Feature;

use Database\Factories\UserFactory;
use Database\Seeders\WebServiceDomainSeeder;
use Database\Seeders\WebServiceSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Phase 5/6 correction — the /admin/web-service-domains page must render the
 * real domain catalog (CHERCHEURS, OFFRES...) from GET /api/v1/admin/domains.
 */
class WebServiceDomainPageRenderTest extends TestCase
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

    // The dedicated page renders its shell with the real table + all modals.
    public function test_admin_domain_page_renders_shell_and_table(): void
    {
        $this->get('/admin/web-service-domains')
            ->assertOk()
            ->assertSee('admin-domains-root')
            ->assertSee('domain-table')
            ->assertSee('domain-tbody')
            ->assertSee('domain-skeleton')
            ->assertSee('domain-empty')
            ->assertSee('domain-create-modal')
            ->assertSee('domain-edit-modal')
            ->assertSee('domain-services-modal');
    }

    // The page carries the marker heading + the list card column headers.
    public function test_admin_domain_page_shows_heading_and_columns(): void
    {
        $this->get('/admin/web-service-domains')
            ->assertOk()
            ->assertSee('Domaines Web Service')
            ->assertSee('Code', false)
            ->assertSee('Statut', false)
            ->assertSee('Actions', false);
    }

    // The GET /api/v1/admin/domains payload shape the page renders from:
    // it must be a top-level `data` array whose items carry service_count,
    // so the table can populate for a non-empty catalog.
    public function test_admin_domains_endpoint_returns_service_count_array(): void
    {
        $this->seed(WebServiceDomainSeeder::class);
        $admin = UserFactory::new()->asAdmin()->create();

        $response = $this->actingAs($admin, 'api')->getJson('/api/v1/admin/domains');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertIsArray($data, 'data must be a top-level array for the page to render.');
        $this->assertNotEmpty($data, 'Catalog must not be empty (CHERCHEURS is seeded).');

        $chercheurs = collect($data)->firstWhere('code', 'CHERCHEURS');
        $this->assertNotNull($chercheurs, 'CHERCHEURS must be present in the catalog.');
        $this->assertArrayHasKey('service_count', $chercheurs, 'Each domain row must expose service_count.');
        $this->assertArrayHasKey('is_active', $chercheurs);
    }

    // Root-cause regression test: when the catalog is non-empty the page's
    // populated-data branch of renderRows() must un-hide #domain-table and
    // #domain-tbody (the inverse of showSkeleton()). Without these two
    // classList.remove('hidden') calls in the populated branch, the rows are
    // written into the DOM but stay display:none and the card renders blank.
    public function test_populated_domain_branch_unhides_table_and_tbody(): void
    {
        $view = file_get_contents(base_path('resources/views/admin/web-service-domains.blade.php'));
        $this->assertIsString($view);

        // Find the populated branch: the block that starts with the empty
        // classList.add('hidden') on the empty-state and builds the rows.
        $needle = "empty.classList.add('hidden')";
        $pos = strpos($view, $needle);
        $this->assertNotFalse($pos, 'Populated branch marker missing.');

        // After that marker, the populated branch must remove 'hidden' from
        // both the table and the tbody so the rendered rows become visible.
        $tail = substr($view, $pos);

        // The branch body (up to the next top-level function) must contain
        // both un-hide calls.
        $branchEnd = strpos($tail, 'function toggleDomainStatus', 0);
        $branch = $branchEnd !== false ? substr($tail, 0, $branchEnd) : $tail;

        $this->assertStringContainsString(
            "tableEl.classList.remove('hidden')",
            $branch,
            'Populated branch must un-hide #domain-table.'
        );
        $this->assertStringContainsString(
            "tbody.classList.remove('hidden')",
            $branch,
            'Populated branch must un-hide #domain-tbody.'
        );
    }
}
