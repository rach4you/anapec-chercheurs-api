<?php

namespace Tests\Feature;

use Database\Seeders\WebServiceDomainSeeder;
use Database\Seeders\WebServiceSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Phase 5 — Admin Domain Management UI (/admin/web-service-domains).
 *
 * The page loads data client-side through the existing Phase 2 API endpoints
 * (GET /api/v1/admin/domains, GET /api/v1/admin/domains/{code},
 * PUT /api/v1/admin/domains/{code}/services, etc.). These tests verify the
 * route renders the view and its structural markers are present.
 */
class WebServiceDomainAdminUiTest extends TestCase
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

    // The route renders the domains list view.
    public function test_domains_route_renders_the_list_view(): void
    {
        $response = $this->get('/admin/web-service-domains');

        $response->assertOk();
        $response->assertSee('admin-domains-root');
        $response->assertSee('Domaines Web Service');
    }

    // The create / edit / manage-services modals are present in the view.
    public function test_domains_view_contains_create_edit_and_services_modals(): void
    {
        $this->get('/admin/web-service-domains')
            ->assertOk()
            ->assertSee('domain-create-modal')
            ->assertSee('domain-edit-modal')
            ->assertSee('domain-services-modal')
            ->assertSee('create-domain-code')
            ->assertSee('edit-domain-code')
            ->assertSee('domain-services-list');
    }

    // The table card is present with a row-actions column.
    public function test_domains_view_contains_the_table(): void
    {
        $this->get('/admin/web-service-domains')
            ->assertOk()
            ->assertSee('domain-table')
            ->assertSee('domain-tbody')
            ->assertSee('domain-empty')
            ->assertSee('domain-skeleton');
    }

    // A non-admin reaching the static shell still gets a 200 page (the client
    // gate + backend `can:admin` handle the real authorization; this mirrors
    // the established pattern of the other admin pages).
    public function test_domains_route_renders_even_when_unauthenticated(): void
    {
        $this->get('/admin/web-service-domains')->assertOk();
    }

    // The named route resolves to the expected path.
    public function test_named_route_points_to_the_domains_view(): void
    {
        $this->assertStringEndsWith('/admin/web-service-domains', route('admin.web-service-domains'));
    }
}
