<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Environment;

// TODO:
// implemented only the simple tests for Environment class since this is where I had the most issues during development
// definitely need some unit tests on the API endpoints if I have time
class EnvironmentTest extends TestCase
{
    protected function tearDown(): void
    {
        // Clean up test environment variables
        unset($_ENV['TEST_VAR']);
        unset($_SERVER['TEST_VAR']);
    }

    public function testGetEnvironmentVariableFromEnv(): void
    {
        $_ENV['TEST_VAR'] = 'test_value';

        $result = Environment::get('TEST_VAR');

        $this->assertEquals('test_value', $result);
    }

    public function testGetEnvironmentVariableWithDefault(): void
    {
        $result = Environment::get('NON_EXISTENT_VAR', 'default_value');

        $this->assertEquals('default_value', $result);
    }

    public function testEnvironmentVariablePriority(): void
    {
        // Test that $_ENV has priority over $_SERVER
        $_ENV['TEST_VAR'] = 'env_value';
        $_SERVER['TEST_VAR'] = 'server_value';

        $result = Environment::get('TEST_VAR');

        $this->assertEquals('env_value', $result, 'Environment variable should take priority over server variable');
    }
}
