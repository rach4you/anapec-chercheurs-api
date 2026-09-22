<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserWebService;
use App\Models\WebService;
use Database\Factories\UserFactory;
use Database\Seeders\WebServiceSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Phase 4.2 — single Web Service permission management.
 *
 * Covers:
 * - PATCH /api/v1/admin/users/{id}/web-services/{code}
 * - DELETE /api/v1/admin/users/{id}/web-services
 * - Regression of the existing PUT full-replace contract.
 *
 * Oracle-safe: the schema is already live, so every test is wrapped in a
 * transaction that is rolled back and the four canonical Web Services are
 * ensured with the idempotent seeder. No migration is required.
 */
class WebServiceSinglePermissionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Connections that should be wrapped in a transaction.
     *
     * @var list<string|null>
     */
    protected $connectionsToTransact = [null];

    protected function setUp(): void
    {
        parent::setUp();

        $this->beginDatabaseTransaction();
        $this->seed(WebServiceSeeder::class);
    }

    private function admin(): User
    {
        return UserFactory::new()->asAdmin()->create(['email' => 'sp-admin@example.com', 'password' => 'secret123']);
    }

    private function user(string $email = 'sp-target@example.com'): User
    {
        return UserFactory::new()->create(['email' => $email, 'password' => 'secret123']);
    }

    private function ws(string $code): WebService
    {
        return WebService::query()->where('code', $code)->firstOrFail();
    }

    private function attach(User $user, string $code, bool $enabled = true): void
    {
        $user->webServices()->attach($this->ws($code)->id, ['is_enabled' => $enabled]);
    }

    private function row(User $user, string $code): ?UserWebService
    {
        return UserWebService::query()
            ->where('user_id', $user->id)
            ->where('web_service_id', $this->ws($code)->id)
            ->first();
    }

    // 1. Admin PATCH existing enabled -> false
    public function test_admin_can_disable_an_existing_permission(): void
    {
        $admin = $this->admin();
        $target = $this->user('sp-disable@example.com');
        $this->attach($target, 'WS_CHECK_CIN');
        $this->assertTrue($target->fresh()->canConsumeWebService('WS_CHECK_CIN'));

        $response = $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/users/'.$target->id.'/web-services/WS_CHECK_CIN', ['is_enabled' => false]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Permission updated.')
            ->assertJsonPath('data.code', 'WS_CHECK_CIN')
            ->assertJsonPath('data.is_enabled', false)
            ->assertJsonPath('data.effective_access', false);

        $row = $this->row($target, 'WS_CHECK_CIN');
        $this->assertNotNull($row, 'The pivot row must remain after disabling.');
        $this->assertFalse((bool) $row->is_enabled);
        $this->assertFalse($target->fresh()->canConsumeWebService('WS_CHECK_CIN'));
    }

    // 2. Admin PATCH existing disabled -> true
    public function test_admin_can_reenable_a_disabled_permission(): void
    {
        $admin = $this->admin();
        $target = $this->user('sp-reenable@example.com');
        $this->attach($target, 'WS_PROFILE', false);
        $this->assertFalse($target->fresh()->canConsumeWebService('WS_PROFILE'));

        $response = $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/users/'.$target->id.'/web-services/WS_PROFILE', ['is_enabled' => true]);

        $response->assertOk()
            ->assertJsonPath('message', 'Permission updated.')
            ->assertJsonPath('data.code', 'WS_PROFILE')
            ->assertJsonPath('data.global_is_active', true)
            ->assertJsonPath('data.is_enabled', true)
            ->assertJsonPath('data.effective_access', true);

        $this->assertTrue($target->fresh()->canConsumeWebService('WS_PROFILE'));
    }

    // 3. Admin PATCH missing row -> true creates the pivot row
    public function test_admin_can_grant_a_permission_that_does_not_exist_yet(): void
    {
        $admin = $this->admin();
        $target = $this->user('sp-grant@example.com');
        $this->assertNull($this->row($target, 'WS_CV'));

        $response = $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/users/'.$target->id.'/web-services/WS_CV', ['is_enabled' => true]);

        $response->assertOk()
            ->assertJsonPath('message', 'Permission updated.')
            ->assertJsonPath('data.is_enabled', true);

        $row = $this->row($target, 'WS_CV');
        $this->assertNotNull($row, 'A new pivot row must be created.');
        $this->assertTrue((bool) $row->is_enabled);
        $this->assertTrue($target->fresh()->canConsumeWebService('WS_CV'));
    }

    // 4. Admin PATCH true on a globally inactive service is rejected
    public function test_enabling_a_globally_inactive_service_is_rejected(): void
    {
        $admin = $this->admin();
        $target = $this->user('sp-inactive-enable@example.com');

        $this->ws('WS_BILAN')->update(['is_active' => false]);

        $response = $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/users/'.$target->id.'/web-services/WS_BILAN', ['is_enabled' => true]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', "Web Service 'WS_BILAN' is not active.");

        $this->assertNull($this->row($target, 'WS_BILAN'), 'No pivot row may be created for an inactive service.');
    }

    // 5. Admin PATCH false on a globally inactive service is allowed
    public function test_disabling_a_globally_inactive_service_is_allowed(): void
    {
        $admin = $this->admin();
        $target = $this->user('sp-inactive-disable@example.com');
        $this->attach($target, 'WS_BILAN');

        $this->ws('WS_BILAN')->update(['is_active' => false]);

        $response = $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/users/'.$target->id.'/web-services/WS_BILAN', ['is_enabled' => false]);

        $response->assertOk()
            ->assertJsonPath('message', 'Permission updated.')
            ->assertJsonPath('data.global_is_active', false)
            ->assertJsonPath('data.is_enabled', false)
            ->assertJsonPath('data.effective_access', false);

        $row = $this->row($target, 'WS_BILAN');
        $this->assertNotNull($row);
        $this->assertFalse((bool) $row->is_enabled);
    }

    // 6. Unknown service code -> 404
    public function test_unknown_service_code_returns_404(): void
    {
        $admin = $this->admin();
        $target = $this->user('sp-unknown-svc@example.com');

        $response = $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/users/'.$target->id.'/web-services/WS_DOES_NOT_EXIST', ['is_enabled' => true]);

        $response->assertStatus(404)
            ->assertJsonPath('message', "Web Service 'WS_DOES_NOT_EXIST' does not exist.");
    }

    // 7. Unknown user id -> 404
    public function test_unknown_user_returns_404(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/users/99999999/web-services/WS_CHECK_CIN', ['is_enabled' => true])
            ->assertStatus(404);
    }

    // 8. Missing is_enabled -> 422
    public function test_missing_is_enabled_returns_422(): void
    {
        $admin = $this->admin();
        $target = $this->user('sp-missing-field@example.com');

        $response = $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/users/'.$target->id.'/web-services/WS_CHECK_CIN', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('is_enabled');
    }

    // 9. Non-boolean is_enabled -> 422
    public function test_non_boolean_is_enabled_returns_422(): void
    {
        $admin = $this->admin();
        $target = $this->user('sp-bad-field@example.com');

        $response = $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/users/'.$target->id.'/web-services/WS_CHECK_CIN', ['is_enabled' => 'yes']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('is_enabled');

        $this->assertNull($this->row($target, 'WS_CHECK_CIN'));
    }

    // 10. Regular user PATCH -> 403
    public function test_regular_user_cannot_patch_permission(): void
    {
        $user = $this->user('sp-non-admin@example.com');

        $this->actingAs($user, 'api')
            ->patchJson('/api/v1/admin/users/'.$user->id.'/web-services/WS_CHECK_CIN', ['is_enabled' => true])
            ->assertStatus(403);
    }

    // 11. Unauthenticated PATCH -> 401
    public function test_unauthenticated_patch_returns_401(): void
    {
        $user = $this->user('sp-unauth@example.com');

        $this->patchJson('/api/v1/admin/users/'.$user->id.'/web-services/WS_CHECK_CIN', ['is_enabled' => true])
            ->assertStatus(401);
    }

    // 12. PATCH no-op returns 200, "Permission unchanged.", updated_at untouched
    public function test_patch_no_op_does_not_touch_updated_at(): void
    {
        $admin = $this->admin();
        $target = $this->user('sp-noop@example.com');
        $this->attach($target, 'WS_CHECK_CIN');

        // Let a second pass so that the no-op and the real change land in
        // different clock seconds (Oracle DATE keeps second precision).
        sleep(1);

        // 1. A real change bumps updated_at.
        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/users/'.$target->id.'/web-services/WS_CHECK_CIN', ['is_enabled' => false])
            ->assertOk()
            ->assertJsonPath('message', 'Permission updated.');

        $afterChange = $this->row($target, 'WS_CHECK_CIN')->updated_at;

        // 2. Re-applying the same value is a no-op and must not touch updated_at.
        $response = $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/users/'.$target->id.'/web-services/WS_CHECK_CIN', ['is_enabled' => false]);

        $response->assertOk()
            ->assertJsonPath('message', 'Permission unchanged.')
            ->assertJsonPath('data.is_enabled', false);

        $this->assertSame(
            $afterChange->format('Y-m-d H:i:s'),
            $this->row($target, 'WS_CHECK_CIN')->updated_at->format('Y-m-d H:i:s'),
            'updated_at must not change on a no-op PATCH.'
        );
    }

    // 13. Middleware integration: disable denies, enable allows
    public function test_runtime_access_follows_the_single_permission_change(): void
    {
        $admin = $this->admin();
        $target = $this->user('sp-gate@example.com');
        $this->attach($target, 'WS_CHECK_CIN');

        $disable = $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/users/'.$target->id.'/web-services/WS_CHECK_CIN', ['is_enabled' => false]);
        $disable->assertOk();
        $this->assertFalse($target->fresh()->canConsumeWebService('WS_CHECK_CIN'));

        $enable = $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/users/'.$target->id.'/web-services/WS_CHECK_CIN', ['is_enabled' => true]);
        $enable->assertOk();
        $this->assertTrue($target->fresh()->canConsumeWebService('WS_CHECK_CIN'));
    }

    // 14. DELETE revoke-all clears every pivot row
    public function test_revoke_all_removes_every_permission(): void
    {
        $admin = $this->admin();
        $target = $this->user('sp-revoke@example.com');
        $this->attach($target, 'WS_CHECK_CIN');
        $this->attach($target, 'WS_PROFILE');

        $response = $this->actingAs($admin, 'api')
            ->deleteJson('/api/v1/admin/users/'.$target->id.'/web-services');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertCount(0, $target->fresh()->webServices()->get(), 'Every pivot row must be removed.');
        $this->assertFalse($target->fresh()->canConsumeWebService('WS_CHECK_CIN'));
        $this->assertFalse($target->fresh()->canConsumeWebService('WS_PROFILE'));
    }

    // 15. DELETE revoke-all is idempotent
    public function test_revoke_all_is_idempotent(): void
    {
        $admin = $this->admin();
        $target = $this->user('sp-revoke-twice@example.com');
        $this->attach($target, 'WS_CHECK_CIN');

        $first = $this->actingAs($admin, 'api')
            ->deleteJson('/api/v1/admin/users/'.$target->id.'/web-services');
        $first->assertOk();

        $second = $this->actingAs($admin, 'api')
            ->deleteJson('/api/v1/admin/users/'.$target->id.'/web-services');
        $second->assertOk();

        $this->assertCount(0, $target->fresh()->webServices()->get());
    }

    // 16. DELETE authorization: user 403, unauthenticated 401, unknown user 404
    public function test_revoke_all_is_forbidden_for_regular_users(): void
    {
        $user = $this->user('sp-revoke-auth@example.com');

        $this->actingAs($user, 'api')
            ->deleteJson('/api/v1/admin/users/'.$user->id.'/web-services')
            ->assertStatus(403);
    }

    public function test_revoke_all_is_unauthenticated_without_a_token(): void
    {
        $user = $this->user('sp-revoke-unauth@example.com');

        $this->deleteJson('/api/v1/admin/users/'.$user->id.'/web-services')
            ->assertStatus(401);
    }

    public function test_revoke_all_returns_404_for_an_unknown_user(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'api')
            ->deleteJson('/api/v1/admin/users/99999999/web-services')
            ->assertStatus(404);
    }

    // 17. Regression: the existing PUT full-replace still works
    public function test_existing_put_full_replace_still_works(): void
    {
        $admin = $this->admin();
        $target = $this->user('sp-put-legacy@example.com');

        $this->actingAs($admin, 'api')
            ->putJson('/api/v1/admin/users/'.$target->id.'/web-services', [
                'web_services' => [
                    ['code' => 'WS_CHECK_CIN', 'enabled' => true],
                    ['code' => 'WS_PROFILE', 'enabled' => false],
                ],
            ])
            ->assertOk();

        $this->assertTrue($target->fresh()->canConsumeWebService('WS_CHECK_CIN'));
        $this->assertFalse($target->fresh()->canConsumeWebService('WS_PROFILE'));
    }

    // 18. Regression: PUT with an empty list is still rejected (min:1 stays)
    public function test_put_with_empty_list_remains_rejected(): void
    {
        $admin = $this->admin();
        $target = $this->user('sp-put-empty@example.com');

        $this->actingAs($admin, 'api')
            ->putJson('/api/v1/admin/users/'.$target->id.'/web-services', ['web_services' => []])
            ->assertStatus(422);
    }

    // 19. Regression: PUT with an inactive service is still rejected
    public function test_put_with_inactive_service_remains_rejected(): void
    {
        $admin = $this->admin();
        $target = $this->user('sp-put-inactive@example.com');

        $this->ws('WS_BILAN')->update(['is_active' => false]);

        $this->actingAs($admin, 'api')
            ->putJson('/api/v1/admin/users/'.$target->id.'/web-services', [
                'web_services' => [['code' => 'WS_BILAN', 'enabled' => true]],
            ])
            ->assertStatus(422);
    }

    // 20. Admin GET permission list reflects the single-permission PATCH
    public function test_admin_permission_list_reflects_single_permission_changes(): void
    {
        $admin = $this->admin();
        $target = $this->user('sp-list-reflects@example.com');
        $this->attach($target, 'WS_CHECK_CIN');

        $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/users/'.$target->id.'/web-services/WS_CHECK_CIN', ['is_enabled' => false]);

        $list = $this->actingAs($admin, 'api')
            ->getJson('/api/v1/admin/users/'.$target->id.'/web-services');

        $list->assertOk()->assertJsonCount(1, 'data');

        $entry = collect($list->json('data'))->firstWhere('code', 'WS_CHECK_CIN');
        $this->assertNotNull($entry);
        $this->assertFalse($entry['is_enabled']);
        $this->assertFalse($entry['effective_access']);
    }

    // Disabling a permission that was never granted preserves absence semantics.
    public function test_disabling_a_non_existing_permission_preserves_absence(): void
    {
        $admin = $this->admin();
        $target = $this->user('sp-absence@example.com');
        $this->assertNull($this->row($target, 'WS_CV'));

        $response = $this->actingAs($admin, 'api')
            ->patchJson('/api/v1/admin/users/'.$target->id.'/web-services/WS_CV', ['is_enabled' => false]);

        $response->assertOk()
            ->assertJsonPath('message', 'Permission unchanged.')
            ->assertJsonPath('data.code', 'WS_CV')
            ->assertJsonPath('data.global_is_active', true)
            ->assertJsonPath('data.is_enabled', false)
            ->assertJsonPath('data.effective_access', false);

        $this->assertNull($this->row($target, 'WS_CV'), 'No meaningless disabled row may be created.');
    }
}
