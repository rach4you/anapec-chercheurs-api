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

/**
 * Phase 4 — effective-access integration.
 *
 * Locks in the centralized `hasEffectiveAccessTo` / `effectiveAccessState`
 * semantics under the new access_scope model:
 *
 *   - scope "all": every ACTIVE Web Service is reachable, dynamically.
 *   - scope "selected": only explicitly enabled `api_user_web_services`
 *     rows grant access. Domain grants are a UI grouping only; they never
 *     expand into their contained services.
 */
class EffectiveAccessTest extends TestCase
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

    private function user(string $email = 'ea-user@example.com'): User
    {
        return UserFactory::new()->create(['email' => $email, 'password' => 'secret123']);
    }

    private function service(string $code): WebService
    {
        return WebService::query()->where('code', $code)->firstOrFail();
    }

    private function domaine(): WebServiceDomain
    {
        return WebServiceDomain::query()->where('code', 'CHERCHEURS')->firstOrFail();
    }

    // 1. No grant, no access scope → no access.
    public function test_no_grant_means_no_access(): void
    {
        $user = $this->user();

        $this->assertFalse($user->hasEffectiveAccessTo($this->service('WS_CV')));
    }

    // 2. A user with global scope can reach every active service.
    public function test_global_scope_grants_access_to_all_active_services(): void
    {
        $user = $this->user();
        $user->update(['access_scope' => User::ACCESS_SCOPE_ALL]);

        $this->assertTrue($user->fresh()->hasEffectiveAccessTo($this->service('WS_CV')));
        $this->assertTrue($user->fresh()->canConsumeWebService('WS_CV'));
        $this->assertTrue($user->fresh()->hasEffectiveAccessTo($this->service('WS_BILAN')));
    }

    // 3. A domain grant does NOT automatically expand into its services.
    public function test_domain_grant_alone_does_not_grant_service_access(): void
    {
        $user = $this->user();
        $user->domains()->attach($this->domaine()->id, ['is_enabled' => true]);

        $this->assertFalse($user->fresh()->hasEffectiveAccessTo($this->service('WS_CV')));
    }

    // 4. An inactive service is denied even with global scope.
    public function test_inactive_service_is_denied_even_with_global_scope(): void
    {
        $user = $this->user();
        $user->update(['access_scope' => User::ACCESS_SCOPE_ALL]);
        $this->service('WS_CV')->update(['is_active' => false]);

        $this->assertFalse($user->fresh()->hasEffectiveAccessTo($this->service('WS_CV')->fresh()));
    }

    // 5. An inactive user is denied even with global scope.
    public function test_inactive_user_is_denied_even_with_global_scope(): void
    {
        $user = $this->user();
        $user->update(['access_scope' => User::ACCESS_SCOPE_ALL, 'is_active' => false]);

        $this->assertFalse($user->fresh()->hasEffectiveAccessTo($this->service('WS_CV')));
    }

    // 6. An explicit disabled override blocks access even with global scope.
    public function test_explicit_disabled_override_wins_over_global_scope(): void
    {
        $user = $this->user();
        $user->update(['access_scope' => User::ACCESS_SCOPE_ALL]);
        $user->webServices()->attach($this->service('WS_CV')->id, ['is_enabled' => false]);

        $this->assertFalse($user->fresh()->hasEffectiveAccessTo($this->service('WS_CV')));

        $state = $user->fresh()->effectiveAccessState($this->service('WS_CV'));
        $this->assertTrue($state['override_disabled']);
        $this->assertSame('none', $state['grant_source']);
        $this->assertFalse($state['effective_access']);
    }

    // 7. An explicit enabled override grants access in "selected" scope.
    public function test_explicit_enabled_override_grants_access_in_selected_scope(): void
    {
        $user = $this->user();
        $user->webServices()->attach($this->service('WS_CV')->id, ['is_enabled' => true]);

        $this->assertTrue($user->fresh()->hasEffectiveAccessTo($this->service('WS_CV')));

        $state = $user->fresh()->effectiveAccessState($this->service('WS_CV'));
        $this->assertSame('direct', $state['grant_source']);
    }

    // 8. A service not in any domain is reachable only via a direct override.
    public function test_service_outside_all_domains_needs_direct_override(): void
    {
        $orphan = WebService::query()->create([
            'code' => 'WS_ORPHAN_EA',
            'name' => 'Orphan',
            'is_active' => true,
        ]);
        $user = $this->user();
        $user->domains()->attach($this->domaine()->id, ['is_enabled' => true]);

        $this->assertFalse($user->fresh()->hasEffectiveAccessTo($orphan));

        $user->webServices()->attach($orphan->id, ['is_enabled' => true]);
        $this->assertTrue($user->fresh()->hasEffectiveAccessTo($orphan->fresh()));
    }

    // 9. effectiveAccessState reports the global scope as grant source.
    public function test_state_reports_global_source(): void
    {
        $user = $this->user();
        $user->update(['access_scope' => User::ACCESS_SCOPE_ALL]);

        $state = $user->fresh()->effectiveAccessState($this->service('WS_CV'));
        $this->assertFalse($state['override_disabled']);
        $this->assertSame('global', $state['grant_source']);
        $this->assertTrue($state['effective_access']);
    }

    // 10. Backward compatibility: a user with ONLY a direct permission
    // works correctly under "selected" scope.
    public function test_backward_compat_direct_only_user(): void
    {
        $user = $this->user();
        $user->webServices()->attach($this->service('WS_CV')->id, ['is_enabled' => true]);

        $this->assertTrue($user->fresh()->canConsumeWebService('WS_CV'));
        $this->assertFalse($user->fresh()->canConsumeWebService('WS_BILAN'));
    }

    // 11. The user self-service endpoint reflects the global scope.
    public function test_user_endpoint_reflects_global_scope(): void
    {
        $user = $this->user();
        $user->update(['access_scope' => User::ACCESS_SCOPE_ALL]);

        $data = $this->actingAs($user, 'api')->getJson('/api/v1/user/web-services')->json('data');
        $cv = collect($data)->firstWhere('code', 'WS_CV');

        $this->assertTrue($cv['effective_access']);
        $this->assertSame('global', $cv['grant_source']);
    }

    // 12. Global scope dynamically includes newly created active services.
    public function test_global_scope_includes_newly_created_service(): void
    {
        $user = $this->user();
        $user->update(['access_scope' => User::ACCESS_SCOPE_ALL]);

        $newService = WebService::query()->create([
            'code' => 'WS_NEW_TEST',
            'name' => 'New Test Service',
            'is_active' => true,
        ]);

        $this->assertTrue($user->fresh()->hasEffectiveAccessTo($newService));
    }
}
