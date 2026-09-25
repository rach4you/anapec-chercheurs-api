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

    public function test_openapi_documents_domain_endpoints(): void
    {
        $spec = json_decode(
            $this->get('/api/documentation/openapi')->assertOk()->getContent(),
            true
        );

        $paths = $spec['paths'] ?? [];
        $this->assertArrayHasKey('/admin/domains', $paths, 'Domain list endpoint is documented.');
        $this->assertArrayHasKey('/admin/domains/{code}', $paths, 'Domain detail endpoint is documented.');
        $this->assertArrayHasKey('/admin/domains/{code}/status', $paths, 'Domain status endpoint is documented.');
        $this->assertArrayHasKey('/admin/domains/{code}/services', $paths, 'Domain services endpoint is documented.');
        $this->assertArrayHasKey('/admin/users/{id}/domains', $paths, 'User domains endpoint is documented.');
        $this->assertArrayHasKey('/admin/users/{id}/domains/{code}', $paths, 'User domain grant endpoint is documented.');
    }

    public function test_openapi_document_admin_domains_tag(): void
    {
        $spec = json_decode(
            $this->get('/api/documentation/openapi')->assertOk()->getContent(),
            true
        );

        $tags = array_map(
            static fn ($tag) => $tag['name'] ?? null,
            $spec['tags'] ?? []
        );

        $this->assertContains('Admin - Domains', $tags);
    }

    public function test_user_web_services_permission_schema_includes_domain_fields(): void
    {
        $spec = json_decode(
            $this->get('/api/documentation/openapi')->assertOk()->getContent(),
            true
        );

        $props = $spec['components']['schemas']['UserWebServicePermission']['properties'] ?? [];

        $this->assertArrayHasKey('domain_granted', $props, 'domain_granted is documented.');
        $this->assertArrayHasKey('override_disabled', $props, 'override_disabled is documented.');
        $this->assertArrayHasKey('grant_source', $props, 'grant_source is documented.');
        $this->assertArrayHasKey('effective_access', $props, 'effective_access is documented.');
        $this->assertArrayHasKey('WebServiceDomain', $spec['components']['schemas'] ?? [], 'WebServiceDomain schema is documented.');
    }
}
