<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Phase 4.2 - User details permission UI.
 *
 * The page loads data client-side through the existing
 * GET /api/v1/admin/users/{id}/web-services endpoint merged with the
 * catalogue from GET /api/v1/admin/web-services. No new API route is
 * required for the rendering itself, and the admin-only behavior is
 * enforced by the shared client-side authorization gate plus the
 * `can:admin` gate on those API calls, which is already covered by the
 * existing API test suites.
 */
class WebServicePermissionUiTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = [null];

    // The details route renders through the same Metronic layout as the users
    // list. This guards against the regression that put the page back on the
    // legacy Tailwind layouts.app shell.
    public function test_details_route_renders_the_metronic_layout(): void
    {
        $response = $this->get('/admin/users/1');

        $response->assertOk();
        $response->assertSee('kt_app_sidebar');
        $response->assertSee('kt_app_header');
        $response->assertSee('kt_app_content_container');
        $response->assertSee('style.bundle.css');
    }

    // The legacy Tailwind shell must not come back on the details page.
    public function test_details_route_does_not_use_the_legacy_app_layout(): void
    {
        $response = $this->get('/admin/users/1');

        $response->assertOk();
        $response->assertDontSee('id="app-sidebar"', false);
        $response->assertDontSee('anapec-600', false);
        $response->assertDontSee('fonts.bunny.net', false);
    }

    // The page renders the Metronic section shells: profile, access summary,
    // the access list and the danger zone.
    public function test_details_route_renders_the_permissions_ui(): void
    {
        $response = $this->get('/admin/users/1');

        $response->assertOk();
        $response->assertSee('profile-card');
        $response->assertSee('summary-card');
        $response->assertSee('access-card');
        $response->assertSee('access-body');
        $response->assertSee('overrides-card');
        $response->assertSee('danger-card');
        $response->assertSee('btn-revoke-all');
    }

    // The access summary exposes the scope, effective and configured figures.
    public function test_details_route_renders_the_access_summary(): void
    {
        $response = $this->get('/admin/users/1');

        $response->assertOk();
        $response->assertSee('summary-scope');
        $response->assertSee('summary-effective');
        $response->assertSee('summary-configured');
    }

    // The shared authorization / failure panels are present.
    public function test_details_route_renders_the_state_panels(): void
    {
        $response = $this->get('/admin/users/1');

        $response->assertOk();
        $response->assertSee('auth-loading');
        $response->assertSee('access-denied');
        $response->assertSee('not-found');
        $response->assertSee('load-error');
        $response->assertSee('btn-retry');
        $response->assertSee('toast');
    }

    // The revoke-all confirmation modal follows the existing modal pattern.
    public function test_details_route_renders_the_revoke_all_confirmation(): void
    {
        $response = $this->get('/admin/users/1');

        $response->assertOk();
        $response->assertSee('revoke-modal');
        $response->assertSee('revoke-modal-message');
        $response->assertSee('btn-confirm-revoke');
        $response->assertSee('Révoquer toutes les permissions');
    }

    // The legacy full-list edit modal was replaced by the per-service toggles.
    public function test_legacy_permissions_modal_is_replaced_by_per_service_toggles(): void
    {
        $response = $this->get('/admin/users/1');

        $response->assertOk();
        $response->assertDontSee('id="perm-modal"', false);
        $response->assertDontSee('id="btn-edit-permissions"', false);
    }

    // The user dashboard is untouched by the permission UI work.
    public function test_dashboard_is_not_renamed_or_removed(): void
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('dashboard');
    }
}
