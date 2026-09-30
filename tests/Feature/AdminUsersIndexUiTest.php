<?php

namespace Tests\Feature;

/**
 * Phase 4.3 — Users list UI.
 *
 * The list page must render through the shared Metronic layout (the same one
 * used by the dashboard) and must expose the real Metronic toolbar/table
 * markup so it looks like a native page of the application. The data itself
 * is loaded client-side through the existing GET /api/v1/admin/users
 * endpoint, which is already covered by the API test suites.
 */
class AdminUsersIndexUiTest extends \Tests\TestCase
{
    // The list route renders the Metronic layout, not the legacy Tailwind app.
    public function test_list_route_renders_the_metronic_layout(): void
    {
        $response = $this->get('/admin/users');

        $response->assertOk();
        $response->assertSee('kt_app_sidebar');
        $response->assertSee('kt_app_header');
        $response->assertSee('kt_app_content_container');
        $response->assertSee('style.bundle.css');
    }

    // The toolbar, search, status filter and table come from the Metronic kit.
    public function test_list_route_renders_the_users_table_toolbar(): void
    {
        $response = $this->get('/admin/users');

        $response->assertOk();
        $response->assertSee('users-table');
        $response->assertSee('users-tbody');
        $response->assertSee('user-search');
        $response->assertSee('user-status-filter');
        $response->assertSee('btn-refresh-users');
        $response->assertSee('users-pagination');
        $response->assertSee('users-empty');
        $response->assertSee('btn-new-user');
        $response->assertSee('Créer un utilisateur');
    }

    // Access-state, pagination and error states follow the dashboard patterns.
    public function test_list_route_renders_the_state_panels(): void
    {
        $response = $this->get('/admin/users');

        $response->assertOk();
        $response->assertSee('auth-loading');
        $response->assertSee('access-denied');
        $response->assertSee('load-error');
        $response->assertSee('btn-retry');
        $response->assertSee('toast');
    }

    // The legacy Tailwind layout must not be used by the users page anymore.
    public function test_list_route_does_not_use_the_legacy_app_layout(): void
    {
        $response = $this->get('/admin/users');

        $response->assertOk();
        $response->assertDontSee('id="app-sidebar"', false);
        $response->assertDontSee('anapec-600', false);
        $response->assertDontSee("fonts.bunny.net", false);
    }

    // The named route still resolves to the same URL as before the change.
    public function test_named_route_points_to_the_users_list(): void
    {
        $this->assertStringEndsWith('/admin/users', route('admin.users'));
    }

    // The dashboard keeps its own Metronic layout and is not renamed.
    public function test_dashboard_is_not_renamed_or_removed(): void
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('dashboard')
            ->assertSee('kt_app_sidebar');
    }
}
