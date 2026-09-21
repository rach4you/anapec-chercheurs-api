<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocumentationTest extends TestCase
{
    public function test_swagger_ui_returns_200(): void
    {
        $this->getJson('/api/documentation')
            ->assertStatus(200)
            ->assertSee('swagger-ui', false);
    }

    public function test_openapi_json_returns_valid_spec(): void
    {
        $response = $this->get('/api/documentation/openapi')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json');

        $spec = json_decode($response->getContent(), true);

        $this->assertIsArray($spec);
        $this->assertArrayHasKey('openapi', $spec);
        $this->assertArrayHasKey('info', $spec);
        $this->assertArrayHasKey('paths', $spec);
        $this->assertSame('ANAPEC Chercheurs API', $spec['info']['title']);
    }
}
