<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Integration tests that make actual HTTP requests to the API endpoints.
 * These tests assume the PHP development server is running on localhost:8080
 */
class ApiIntegrationTest extends TestCase
{
    private string $baseUrl = 'http://localhost:8080';

    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true
        ]);

        if ($method === 'POST' && !empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        
        if (curl_error($ch)) {
            $this->fail('cURL error: ' . curl_error($ch));
        }
        
        curl_close($ch);

        return [
            'status_code' => $httpCode,
            'content_type' => $contentType,
            'body' => $response
        ];
    }

    private function isServerRunning(): bool
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/health',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 5
        ]);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $result !== false && $httpCode === 200;
    }

    protected function setUp(): void
    {
        if (!$this->isServerRunning()) {
            $this->markTestSkipped('PHP development server is not running on ' . $this->baseUrl);
        }
    }

    public function testHealthEndpoint(): void
    {
        $response = $this->makeRequest('GET', '/health');
        
        $this->assertEquals(200, $response['status_code']);
        $this->assertStringContainsString('application/json', $response['content_type']);
        
        $data = json_decode($response['body'], true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('ok', $data);
        $this->assertTrue($data['ok']);
    }

    public function testDocsEndpoint(): void
    {
        $response = $this->makeRequest('GET', '/docs');
        
        $this->assertEquals(200, $response['status_code']);
        $this->assertStringContainsString('text/html', $response['content_type']);
        $this->assertStringContainsString('html', strtolower($response['body']));
    }

    public function testApiDocsJsonEndpoint(): void
    {
        $response = $this->makeRequest('GET', '/api-docs.json');
        
        $this->assertEquals(200, $response['status_code']);
        $this->assertStringContainsString('application/json', $response['content_type']);
        
        $data = json_decode($response['body'], true);
        $this->assertIsArray($data);
        // json_decode would return null if invalid
        $this->assertNotNull($data);
    }

    public function testAuthStatusEndpoint(): void
    {
        $response = $this->makeRequest('GET', '/auth/status');
        
        $this->assertEquals(200, $response['status_code']);
        $this->assertStringContainsString('application/json', $response['content_type']);
        
        $data = json_decode($response['body'], true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('configured', $data);
        $this->assertArrayHasKey('connected', $data);
        $this->assertArrayHasKey('error', $data);
        
        // these should be boolean values
        $this->assertIsBool($data['configured']);
        $this->assertIsBool($data['connected']);
        
        // error can be null or string
        $this->assertTrue(is_null($data['error']) || is_string($data['error']));
    }

    public function testSyncAccountsEndpoint(): void
    {
        $response = $this->makeRequest('POST', '/sync/accounts');

        // this endpoint will return 500 if Xero is not properly configured or 200 if it works
        $this->assertContains($response['status_code'], [200, 500]);
        $this->assertStringContainsString('application/json', $response['content_type']);
        
        $data = json_decode($response['body'], true);
        $this->assertIsArray($data);
        
        if ($response['status_code'] === 200) {
            // success response should have count and rows
            $this->assertArrayHasKey('count', $data);
            $this->assertArrayHasKey('rows', $data);
            $this->assertIsInt($data['count']);
            $this->assertIsArray($data['rows']);
        } else {
            // error response should have error message
            $this->assertArrayHasKey('error', $data);
            $this->assertIsString($data['error']);
        }
    }

    public function testSyncVendorsEndpoint(): void
    {
        $response = $this->makeRequest('POST', '/sync/vendors');

        // this endpoint may return 500 if Xero is not properly configured or 200 if it works
        $this->assertContains($response['status_code'], [200, 500]);
        $this->assertStringContainsString('application/json', $response['content_type']);
        
        $data = json_decode($response['body'], true);
        $this->assertIsArray($data);
        
        if ($response['status_code'] === 200) {
            // success response should have count and rows
            $this->assertArrayHasKey('count', $data);
            $this->assertArrayHasKey('rows', $data);
            $this->assertIsInt($data['count']);
            $this->assertIsArray($data['rows']);
        } else {
            // error response should have error message
            $this->assertArrayHasKey('error', $data);
            $this->assertIsString($data['error']);
        }
    }

    public function testNotFoundEndpoint(): void
    {
        $response = $this->makeRequest('GET', '/nonexistent-endpoint');
        
        $this->assertEquals(404, $response['status_code']);
        $this->assertEquals('Not Found', $response['body']);
    }

    public function testOptionsMethod(): void
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/health',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'OPTIONS',
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // OPTIONS should return 204 No Content
        $this->assertEquals(204, $httpCode);
    }
}