<?php
namespace App;


// Environment helper functions

class Environment
{
    // get environment variable value
    // first checks $_ENV, then $_SERVER, then getenv(), because dotenv loads into $_ENV
    public static function get(string $key, $default = false)
    {
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
