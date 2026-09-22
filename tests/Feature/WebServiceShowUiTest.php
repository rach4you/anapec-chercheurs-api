<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Phase 3.6 — Web Service details UI.
 *
 * These tests exercise the web route that renders the details view. The page
 * loads data client-side through the existing
 * GET /api/v1/admin/web-services/{code} endpoint (no new API route is added
 * in this phase). The admin-only behavior is enforced by the shared
 * client-side authorization gate plus the backend `can:admin` gate on that
 * API call, which is already covered by the existing API test suites.
 */
class WebServiceShowUiTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = [null];

    // The details route renders the shared layout + details view.
    public function test_details_route_renders_the_details_view(): void
    {
        $response = $this->get('/admin/web-services/WS_CHECK_CIN');

        $response->assertOk();
        $response->assertSee('admin-ws-show-root');
        $response->assertSee('detail-code');
        $response->assertSee('detail-name');
        $response->assertSee('detail-description');
        $response->assertSee('Retour aux Web Services');
    }

    // The route forwards the code to the view for client-side use.
    public function test_details_route_passes_the_code_to_the_view(): void
    {
        $this->get('/admin/web-services/WS_CV')
            ->assertOk()
            ->assertViewHas('code', 'WS_CV');
    }

    // Codes that do not match [A-Za-z0-9_]+ do not match the route at all.
    public function test_details_route_rejects_invalid_codes_at_router_level(): void
    {
        $this->get('/admin/web-services/foo%20bar')->assertNotFound();
    }

    // The named route resolves to the expected path.
    public function test_named_route_points_to_the_details_view(): void
    {
        $uri = route('admin.web-services.show', ['code' => 'WS_CV']);
        $this->assertStringEndsWith('/admin/web-services/WS_CV', $uri);
    }
}
