<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class SwaggerDocumentationTest extends TestCase
{
    public function test_swagger_ui_is_accessible(): void
    {
        $response = $this->get('/docs');

        $response->assertStatus(200);
        $response->assertSee('Tinder Task API');
    }

    public function test_swagger_json_file_exists(): void
    {
        $jsonPath = storage_path('api-docs/api-docs.json');
        
        $this->assertFileExists($jsonPath);
        
        $content = file_get_contents($jsonPath);
        $data = json_decode($content, true);
        
        $this->assertNotNull($data);
        $this->assertArrayHasKey('openapi', $data);
        $this->assertArrayHasKey('info', $data);
        $this->assertArrayHasKey('paths', $data);
    }

    public function test_swagger_json_contains_expected_endpoints(): void
    {
        $jsonPath = storage_path('api-docs/api-docs.json');
        $content = file_get_contents($jsonPath);
        $data = json_decode($content, true);
        
        // Check that our API endpoints are documented
        $this->assertArrayHasKey('/api/users', $data['paths']);
        $this->assertArrayHasKey('/api/likes', $data['paths']);
        
        // Check that GET and POST methods are documented for users
        $this->assertArrayHasKey('get', $data['paths']['/api/users']);
        
        // Check that GET and POST methods are documented for likes
        $this->assertArrayHasKey('get', $data['paths']['/api/likes']);
        $this->assertArrayHasKey('post', $data['paths']['/api/likes']);
        
        // Verify API info
        $this->assertEquals('Tinder Task API', $data['info']['title']);
        $this->assertEquals('1.0.0', $data['info']['version']);
    }
}
