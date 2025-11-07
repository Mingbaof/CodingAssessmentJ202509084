<?php
namespace App;


// Environment helper functions

class Environment
{
    // Get environment variable value
    // First checks $_ENV, then $_SERVER, then getenv()
    public static function get(string $key, $default = false)
    {
        # dotenv loads into $_ENV
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        
        $envValue = getenv($key);
        if ($envValue !== false) {
            return $envValue;
        }
        
        return $default;
    }
}
