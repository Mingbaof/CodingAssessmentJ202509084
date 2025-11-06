<?php
namespace App;

/**
 * Environment helper functions
 */
class Environment
{
    /**
     * Get environment variable value
     * First checks $_ENV, then $_SERVER, then getenv()
     */
    public static function get(string $key, $default = false)
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
    }
}
