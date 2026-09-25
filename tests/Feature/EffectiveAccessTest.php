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
 * semantics: user-active + service-active + (direct OR active-domain grant),
 * with an explicit disabled override always winning.
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

    // 1. No domain grant, no direct permission → no access.
    public function test_no_grant_means_no_access(): void
    {
        $user = $this->user();

        $this->assertFalse($user->hasEffectiveAccessTo($this->service('WS_CV')));
    }

    // 2. An enabled, active domain containing the service grants access.
    public function test_domain_grant_grants_access(): void
    {
        $user = $this->user();
        $user->domains()->attach($this->domaine()->id, ['is_enabled' => true]);

        $this->assertTrue($user->hasEffectiveAccessTo($this->service('WS_CV')));
        $this->assertTrue($user->canConsumeWebService('WS_CV'));
    }

    // 3. A disabled domain grant does not grant access.
    public function test_disabled_domain_grant_does_not_grant(): void
    {
        $user = $this->user();
        $user->domains()->attach($this->domaine()->id, ['is_enabled' => false]);

        $this->assertFalse($user->hasEffectiveAccessTo($this->service('WS_CV')));
    }

    // 4. An inactive domain does not grant access.
    public function test_inactive_domain_does_not_grant(): void
    {
        $user = $this->user();
        $user->domains()->attach($this->domaine()->id, ['is_enabled' => true]);
        $this->domaine()->update(['is_active' => false]);

        $this->assertFalse($user->hasEffectiveAccessTo($this->service('WS_CV')));
    }

    // 5. An inactive service is denied even with a domain grant.
    public function test_inactive_service_is_denied(): void
    {
        $user = $this->user();
        $user->domains()->attach($this->domaine()->id, ['is_enabled' => true]);
        $this->service('WS_CV')->update(['is_active' => false]);

        $this->assertFalse($user->hasEffectiveAccessTo($this->service('WS_CV')->fresh()));
    }

    // 6. An inactive user is denied even with a domain grant.
    public function test_inactive_user_is_denied(): void
    {
        $user = $this->user();
        $user->domains()->attach($this->domaine()->id, ['is_enabled' => true]);
        $user->update(['is_active' => false]);

        $this->assertFalse($user->fresh()->hasEffectiveAccessTo($this->service('WS_CV')));
    }

    // 7. An explicit disabled override blocks access even when a domain grants it.
    public function test_explicit_disabled_override_wins_over_domain(): void
    {
        $user = $this->user();
        $user->domains()->attach($this->domaine()->id, ['is_enabled' => true]);
        $user->webServices()->attach($this->service('WS_CV')->id, ['is_enabled' => false]);

        $this->assertFalse($user->hasEffectiveAccessTo($this->service('WS_CV')));

        $state = $user->effectiveAccessState($this->service('WS_CV'));
        $this->assertTrue($state['override_disabled']);
        $this->assertSame('override_disabled', $state['grant_source']);
    }

    // 8. An explicit enabled override grants access even without a domain.
    public function test_explicit_enabled_override_wins_without_domain(): void
    {
        $user = $this->user();
        $user->webServices()->attach($this->service('WS_CV')->id, ['is_enabled' => true]);

        $this->assertTrue($user->hasEffectiveAccessTo($this->service('WS_CV')));

        $state = $user->effectiveAccessState($this->service('WS_CV'));
        $this->assertSame('direct', $state['grant_source']);
    }

    // 9. A service not in any domain is reachable only via a direct override.
    public function test_service_outside_all_domains_needs_direct_override(): void
    {
        // Build an isolated service not attached to the CHERCHEURS domain.
        $orphan = WebService::query()->create([
            'code' => 'WS_ORPHAN_EA',
            'name' => 'Orphan',
            'is_active' => true,
        ]);
        $user = $this->user();
        $user->domains()->attach($this->domaine()->id, ['is_enabled' => true]);

        $this->assertFalse($user->hasEffectiveAccessTo($orphan));

        $user->webServices()->attach($orphan->id, ['is_enabled' => true]);
        $this->assertTrue($user->fresh()->hasEffectiveAccessTo($orphan->fresh()));
    }

    // 10. effectiveAccessState reports domain as the grant source.
    public function test_state_reports_domain_source(): void
    {
        $user = $this->user();
        $user->domains()->attach($this->domaine()->id, ['is_enabled' => true]);

        $state = $user->effectiveAccessState($this->service('WS_CV'));
        $this->assertTrue($state['domain_granted']);
        $this->assertFalse($state['override_disabled']);
        $this->assertSame('domain', $state['grant_source']);
        $this->assertTrue($state['effective_access']);
    }

    // 11. Backward compatibility: a user with ONLY a direct permission is
    // unaffected by the existence of the domain infrastructure.
    public function test_backward_compat_direct_only_user(): void
    {
        $user = $this->user();
        $user->webServices()->attach($this->service('WS_CV')->id, ['is_enabled' => true]);

        $this->assertTrue($user->canConsumeWebService('WS_CV'));

        // A service the user has NO direct row for and NO domain grant for
        // stays denied.
        $this->assertFalse($user->canConsumeWebService('WS_BILAN'));
    }

    // 12. The user self-service endpoint reflects the domain grant.
    public function test_user_endpoint_reflects_domain_grant(): void
    {
        $user = $this->user();
        $user->domains()->attach($this->domaine()->id, ['is_enabled' => true]);

        $data = $this->actingAs($user, 'api')->getJson('/api/v1/user/web-services')->json('data');
        $cv = collect($data)->firstWhere('code', 'WS_CV');

        $this->assertTrue($cv['domain_granted']);
        $this->assertTrue($cv['effective_access']);
        $this->assertSame('domain', $cv['grant_source']);
    }
}
