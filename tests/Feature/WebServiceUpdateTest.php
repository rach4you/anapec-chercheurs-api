<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WebService;
use Database\Factories\UserFactory;
use Database\Seeders\WebServiceSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class WebServiceUpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = [null];

    protected function setUp(): void
    {
        parent::setUp();

        $this->beginDatabaseTransaction();
        $this->seed(WebServiceSeeder::class);
    }

    private function admin(): User
    {
        return UserFactory::new()->asAdmin()->create(['email' => 'ws-update-admin@example.com', 'password' => 'secret123']);
    }

    private function user(): User
    {
        return UserFactory::new()->create(['email' => 'ws-update-user@example.com', 'password' => 'secret123']);
    }

    private function service(string $code): WebService
    {
        return WebService::query()->where('code', $code)->firstOrFail();
    }

    // 1. Admin can update name.
    public function test_admin_can_update_name(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV', [
                'name' => 'Résumé (CV)',
                'description' => $this->service('WS_CV')->description,
            ])
            ->assertOk()
            ->assertJsonPath('data.code', 'WS_CV');

        $this->assertSame('Résumé (CV)', $this->service('WS_CV')->fresh()->name);
    }

    // 2. Admin can update description.
    public function test_admin_can_update_description(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->patchJson('/api/v1/admin/web-services/WS_PROFILE', [
                'name' => $this->service('WS_PROFILE')->name,
                'description' => 'Updated description.',
            ])
            ->assertOk();

        $this->assertSame('Updated description.', $this->service('WS_PROFILE')->fresh()->description);
    }

    // 3. Admin can update both name and description.
    public function test_admin_can_update_name_and_description(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV', [
                'name' => 'Curriculum Vitae v2',
                'description' => 'Both updated together.',
            ])
            ->assertOk();

        $service = $this->service('WS_CV')->fresh();
        $this->assertSame('Curriculum Vitae v2', $service->name);
        $this->assertSame('Both updated together.', $service->description);
    }

    // 4. Response is HTTP 200.
    public function test_update_returns_200(): void
    {
        $response = $this->actingAs($this->admin(), 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV', [
                'name' => 'CV',
            ]);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    // 5. Response contains the WebServiceResource fields.
    public function test_update_returns_web_service_resource_fields(): void
    {
        $response = $this->actingAs($this->admin(), 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV', [
                'name' => 'CV Updated',
                'description' => 'Description updated.',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Web Service updated.');

        $data = $response->json('data');
        foreach (['id', 'code', 'name', 'description', 'is_active', 'created_at', 'updated_at'] as $field) {
            $this->assertArrayHasKey($field, $data, 'Response data must include "'.$field.'".');
        }

        $this->assertSame('CV Updated', $data['name']);
        $this->assertSame('Description updated.', $data['description']);
        $this->assertSame('WS_CV', $data['code']);
    }

    // 6. Database contains the updated name/description.
    public function test_database_contains_updated_values(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV', [
                'name' => 'Database Verified Name',
                'description' => 'Database Verified Description',
            ])
            ->assertOk();

        $service = $this->service('WS_CV')->fresh();
        $this->assertSame('Database Verified Name', $service->name);
        $this->assertSame('Database Verified Description', $service->description);
    }

    // 7. Code remains unchanged even when a different code is sent in the payload.
    public function test_code_remains_unchanged(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV', [
                'name' => 'CV',
                'code' => 'WS_SOMETHING_ELSE',
            ])
            ->assertOk();

        $service = $this->service('WS_CV')->fresh();
        $this->assertSame('WS_CV', $service->code, 'The code must remain immutable');
        $this->assertNull(
            WebService::query()->where('code', 'WS_SOMETHING_ELSE')->first(),
            'A new code must never be created through the update endpoint'
        );
    }

    // 8. is_active remains unchanged even when a different value is sent.
    public function test_is_active_remains_unchanged(): void
    {
        $this->service('WS_CV')->update(['is_active' => false]);

        $this->actingAs($this->admin(), 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV', [
                'name' => 'CV Still Inactive',
                'is_active' => true,
            ])
            ->assertOk();

        $this->assertFalse(
            $this->service('WS_CV')->fresh()->is_active,
            'The metadata endpoint must not flip the global status'
        );
    }

    // 9. A regular user cannot update Web Service metadata.
    public function test_regular_user_cannot_update_metadata(): void
    {
        $this->actingAs($this->user(), 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV', [
                'name' => 'Hacked Name',
            ])
            ->assertStatus(403);

        $this->assertNotSame('Hacked Name', $this->service('WS_CV')->fresh()->name);
    }

    // 10. Unauthenticated requests are rejected.
    public function test_unauthenticated_user_cannot_update_metadata(): void
    {
        $this->patchJson('/api/v1/admin/web-services/WS_CV', [
            'name' => 'Unauthenticated Name',
        ])->assertStatus(401);
    }

    // 11. Unknown service code → 404.
    public function test_unknown_code_returns_404(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->patchJson('/api/v1/admin/web-services/WS_DOES_NOT_EXIST', [
                'name' => 'Nope',
            ])
            ->assertStatus(404);
    }

    // 12. Missing `name` → 422 with a field error.
    public function test_missing_name_returns_422(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV', [
                'description' => 'Only a description',
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['name']]);
    }

    // 13. `name` longer than 255 characters → 422.
    public function test_name_over_255_chars_returns_422(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV', [
                'name' => str_repeat('a', 256),
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['name']]);
    }

    // 14. `description` longer than 500 characters → 422.
    public function test_description_over_500_chars_returns_422(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV', [
                'name' => 'CV',
                'description' => str_repeat('a', 501),
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['description']]);
    }

    // 15. Unexpected fields cannot modify `code`.
    public function test_unexpected_fields_cannot_modify_code(): void
    {
        $originalCode = $this->service('WS_CV')->code;

        $this->actingAs($this->admin(), 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV', [
                'name' => 'CV',
                'code' => 'WS_EVIL',
                'is_active' => true,
                'id' => 99999,
                'password' => 'irrelevant',
            ])
            ->assertOk();

        $this->assertSame($originalCode, $this->service('WS_CV')->fresh()->code);
        $this->assertNull(WebService::query()->where('code', 'WS_EVIL')->first());
    }

    // 16. Existing Web Services remain intact after an update of one service.
    public function test_existing_web_services_remain_intact(): void
    {
        $others = collect(WebService::query()->get())
            ->reject(fn (WebService $service) => $service->code === 'WS_CV')
            ->map(fn (WebService $service) => [
                'code' => $service->code,
                'name' => $service->name,
                'description' => $service->description,
                'is_active' => $service->is_active,
            ])
            ->all();

        $this->actingAs($this->admin(), 'api')
            ->patchJson('/api/v1/admin/web-services/WS_CV', [
                'name' => 'CV Renamed',
                'description' => 'CV Redescribed',
            ])
            ->assertOk();

        $stillThere = collect(WebService::query()->get())
            ->reject(fn (WebService $service) => $service->code === 'WS_CV')
            ->map(fn (WebService $service) => [
                'code' => $service->code,
                'name' => $service->name,
                'description' => $service->description,
                'is_active' => $service->is_active,
            ])
            ->all();

        $this->assertSame($others, $stillThere, 'Services other than the one being updated must remain intact');
        $this->assertNotNull($this->service('WS_BILAN')->fresh());
        $this->assertNotNull($this->service('WS_CHECK_CIN')->fresh());
        $this->assertNotNull($this->service('WS_PROFILE')->fresh());
    }
}
