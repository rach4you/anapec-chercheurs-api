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

    // The details route renders the permissions card and the per-service toggles.
    public function test_details_route_renders_the_permissions_ui(): void
    {
        $response = $this->get('/admin/users/1');

        $response->assertOk();
        $response->assertSee('permissions-card');
        $response->assertSee('permissions-skeleton');
        $response->assertSee('permissions-body');
        $response->assertSee('btn-revoke-perms');
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
