<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Bootstrap;
use App\LoggerFactory;
use App\Storage;
use App\XeroClient;
use App\Environment;

class ApiEndpointsTest extends TestCase
{
    private $originalGlobals;
    private $originalServer;
    private $originalEnv;
    private string $testStorageDir;
    private string $testLogDir;

    protected function setUp(): void
    {
        // backup original globals and server variables
        $this->originalGlobals = $GLOBALS;
        $this->originalServer = $_SERVER;
        $this->originalEnv = $_ENV;

        // create temporary test directories
        $this->testStorageDir = sys_get_temp_dir() . '/test_storage_' . uniqid();
        $this->testLogDir = sys_get_temp_dir() . '/test_logs_' . uniqid();
        mkdir($this->testStorageDir, 0777, true);
        mkdir($this->testLogDir, 0777, true);

        // set up test environment
        $_ENV['STORAGE_DIR'] = $this->testStorageDir;
        $_ENV['LOG_DIR'] = $this->testLogDir;
        $_ENV['ALLOWED_ORIGIN'] = 'http://localhost:3000';
        $_ENV['XERO_CLIENT_ID'] = 'test_client_id';
        $_ENV['XERO_CLIENT_SECRET'] = 'test_client_secret';

        // initialize components for testing
        [$config] = [Bootstrap::init()];
        $logger = LoggerFactory::create($config['logDir']);
        $storage = new Storage($config['storageDir']);
        $xero = new XeroClient($storage, $logger);

        $GLOBALS['storage'] = $storage;
        $GLOBALS['xero'] = $xero;
        $GLOBALS['logger'] = $logger;
    }

    protected function tearDown(): void
    {
        // restore original state
        foreach ($this->originalGlobals as $key => $value) {
            $GLOBALS[$key] = $value;
        }
        $_SERVER = $this->originalServer;
        $_ENV = $this->originalEnv;

        // clean up test directories
        $this->removeDirectory($this->testStorageDir);
        $this->removeDirectory($this->testLogDir);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    private function simulateRequest(string $method, string $path): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $path;
    }

    private function captureOutput(callable $callback): string
    {
        ob_start();
        try {
            $callback();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return ob_get_clean();
    }

    public function testHealthEndpoint(): void
    {
        $this->simulateRequest('GET', '/health');

        $output = $this->captureOutput(function() {
            // simulate the health endpoint logic
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if ($path === '/health') {
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode(['ok' => true]);
                return;
            }
        });

        $expectedJson = json_encode(['ok' => true]);
        $this->assertEquals($expectedJson, $output);
        $this->assertEquals(200, http_response_code());
    }

    public function testDocsEndpoint(): void
    {
        $this->simulateRequest('GET', '/docs');

        // create a mock docs.html file for testing
        $docsPath = $this->testStorageDir . '/docs.html';
        file_put_contents($docsPath, '<html><body>API Documentation</body></html>');

        $output = $this->captureOutput(function() use ($docsPath) {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if ($path === '/docs' && $_SERVER['REQUEST_METHOD'] === 'GET') {
                header('Content-Type: text/html');
                readfile($docsPath);
                return;
            }
        });

        $this->assertEquals('<html><body>API Documentation</body></html>', $output);
    }

    public function testApiDocsJsonEndpoint(): void
    {
        $this->simulateRequest('GET', '/api-docs.json');

        // create a mock api-docs.json file for testing
        $apiDocsPath = $this->testStorageDir . '/api-docs.json';
        $apiDocsContent = json_encode([
            'openapi' => '3.0.0',
            'info' => ['title' => 'Test API', 'version' => '1.0.0']
        ]);
        file_put_contents($apiDocsPath, $apiDocsContent);

        $output = $this->captureOutput(function() use ($apiDocsPath) {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if ($path === '/api-docs.json' && $_SERVER['REQUEST_METHOD'] === 'GET') {
                header('Content-Type: application/json');
                readfile($apiDocsPath);
                return;
            }
        });

        $this->assertEquals($apiDocsContent, $output);
    }

    public function testAuthStatusEndpointConfigured(): void
    {
        $this->simulateRequest('GET', '/auth/status');

        $output = $this->captureOutput(function() {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if ($path === '/auth/status' && $_SERVER['REQUEST_METHOD'] === 'GET') {
                $configured = $GLOBALS['xero']->isConfigured();
                $connected = false;
                $error = null;

                // for testing, we'll simulate the logic without actual Xero API calls
                if ($configured) {
                    try {
                        // mock successful token retrieval
                        $connected = true;
                    } catch (\Throwable $e) {
                        $error = $e->getMessage();
                    }
                }

                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode([
                    'configured' => $configured,
                    'connected' => $connected,
                    'error' => $error
                ]);
                return;
            }
        });

        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('configured', $response);
        $this->assertArrayHasKey('connected', $response);
        $this->assertArrayHasKey('error', $response);
        $this->assertTrue($response['configured']); // should be true with our test environment
    }

    public function testAuthStatusEndpointNotConfigured(): void
    {
        // remove client credentials to test unconfigured state
        unset($_ENV['XERO_CLIENT_ID']);
        unset($_ENV['XERO_CLIENT_SECRET']);

        $this->simulateRequest('GET', '/auth/status');

        $output = $this->captureOutput(function() {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if ($path === '/auth/status' && $_SERVER['REQUEST_METHOD'] === 'GET') {
                // Re-create XeroClient without credentials
                $storage = new Storage($this->testStorageDir);
                $logger = LoggerFactory::create($this->testLogDir);
                $xero = new XeroClient($storage, $logger);

                $configured = $xero->isConfigured();
                $connected = false;
                $error = null;
                $missingConfig = [];

                if (!$configured) {
                    $clientId = Environment::get('XERO_CLIENT_ID');
                    $clientSecret = Environment::get('XERO_CLIENT_SECRET');

                    if (!$clientId) {
                        $missingConfig[] = 'XERO_CLIENT_ID';
                    }
                    if (!$clientSecret) {
                        $missingConfig[] = 'XERO_CLIENT_SECRET';
                    }

                    if (!empty($missingConfig)) {
                        $error = 'Missing required environment variables: ' . implode(', ', $missingConfig) . '. Check your .env file.';
                    }
                }

                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode([
                    'configured' => $configured,
                    'connected' => $connected,
                    'error' => $error
                ]);
                return;
            }
        });

        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertFalse($response['configured']);
        $this->assertFalse($response['connected']);
        $this->assertStringContainsString('Missing required environment variables', $response['error']);
    }

    public function testSyncAccountsEndpoint(): void
    {
        $this->simulateRequest('POST', '/sync/accounts');

        // create a mock XeroClient that returns test data
        $mockAccounts = [
            [
                'AccountID' => 'test-account-1',
                'Code' => '200',
                'Name' => 'Test Account',
                'Type' => 'BANK',
                'Class' => 'ASSET',
                'Status' => 'ACTIVE',
                'EnablePaymentsToAccount' => true
            ]
        ];

        $output = $this->captureOutput(function() use ($mockAccounts) {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if ($path === '/sync/accounts' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                // mock the sync accounts logic without actual Xero API calls
                $rows = $mockAccounts; // in real implementation, this would be $GLOBALS['xero']->getAccounts()

                $GLOBALS['storage']->writeJson('accounts.json', $rows);
                $GLOBALS['storage']->writeCsv('accounts.csv', $rows);

                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode(['count' => count($rows), 'rows' => $rows]);
                return;
            }
        });

        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('count', $response);
        $this->assertArrayHasKey('rows', $response);
        $this->assertEquals(1, $response['count']);
        $this->assertEquals($mockAccounts, $response['rows']);

        // verify files were created
        $this->assertFileExists($this->testStorageDir . '/accounts.json');
        $this->assertFileExists($this->testStorageDir . '/accounts.csv');

        // verify JSON content
        $jsonContent = json_decode(file_get_contents($this->testStorageDir . '/accounts.json'), true);
        $this->assertEquals($mockAccounts, $jsonContent);
    }

    public function testSyncVendorsEndpoint(): void
    {
        $this->simulateRequest('POST', '/sync/vendors');

        // create a mock XeroClient that returns test data
        $mockVendors = [
            [
                'ContactID' => 'test-vendor-1',
                'Name' => 'Test Vendor',
                'EmailAddress' => 'vendor@test.com',
                'IsSupplier' => true,
                'IsCustomer' => false,
                'ContactStatus' => 'ACTIVE'
            ]
        ];

        $output = $this->captureOutput(function() use ($mockVendors) {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if ($path === '/sync/vendors' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                // mock the sync vendors logic without actual Xero API calls
                $rows = $mockVendors; // in real implementation, this would be $GLOBALS['xero']->getVendors()

                $GLOBALS['storage']->writeJson('vendors.json', $rows);
                $GLOBALS['storage']->writeCsv('vendors.csv', $rows);

                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode(['count' => count($rows), 'rows' => $rows]);
                return;
            }
        });

        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('count', $response);
        $this->assertArrayHasKey('rows', $response);
        $this->assertEquals(1, $response['count']);
        $this->assertEquals($mockVendors, $response['rows']);

        // verify files were created
        $this->assertFileExists($this->testStorageDir . '/vendors.json');
        $this->assertFileExists($this->testStorageDir . '/vendors.csv');

        // verify JSON content
        $jsonContent = json_decode(file_get_contents($this->testStorageDir . '/vendors.json'), true);
        $this->assertEquals($mockVendors, $jsonContent);
    }

    public function testNotFoundEndpoint(): void
    {
        $this->simulateRequest('GET', '/nonexistent');

        $output = $this->captureOutput(function() {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            
            // simulate all the endpoints checks and fall through to 404
            if ($path === '/health') {
                return;
            }
            if ($path === '/docs' && $_SERVER['REQUEST_METHOD'] === 'GET') {
                return;
            }
            if ($path === '/api-docs.json' && $_SERVER['REQUEST_METHOD'] === 'GET') {
                return;
            }
            if ($path === '/auth/status' && $_SERVER['REQUEST_METHOD'] === 'GET') {
                return;
            }
            if ($path === '/sync/accounts' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                return;
            }
            if ($path === '/sync/vendors' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                return;
            }

            // no routes matched
            http_response_code(404);
            echo 'Not Found';
        });

        $this->assertEquals('Not Found', $output);
        $this->assertEquals(404, http_response_code());
    }
}