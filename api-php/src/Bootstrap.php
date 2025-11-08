<?php
namespace App;

use Dotenv\Dotenv;

class Bootstrap
{
    public static function init(): array
    {
        $root = dirname(__DIR__);
        if (is_file($root.'/.env')) {
            $dotenv = Dotenv::createImmutable($root);
            $dotenv->load(); // read .env and load into $_ENV
        }
        
        $storageDir = Environment::get('STORAGE_DIR');
        if (!$storageDir) {
            $storageDir = __DIR__.'/../storage';
        } else {
            // if relative path in .env, make it relative to project root
            if (!str_starts_with($storageDir, '/')) {
                $storageDir = __DIR__.'/../'.$storageDir;
            }
        }
        
        $logDir = Environment::get('LOG_DIR');
        if (!$logDir) {
            $logDir = __DIR__.'/../logs';
        } else {
            // if relative path in .env, make it relative to project root
            if (!str_starts_with($logDir, '/')) {
                $logDir = __DIR__.'/../'.$logDir;
            }
        }
        
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0777, true);
        }
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        // Getting absolute paths
        $realStorageDir = realpath($storageDir);
        if (!$realStorageDir) {
            $realStorageDir = $storageDir;
        }
        
        $realLogDir = realpath($logDir);
        if (!$realLogDir) {
            $realLogDir = $logDir;
        }
        
        $allowedOrigin = Environment::get('ALLOWED_ORIGIN');
        if (!$allowedOrigin) {
            $allowedOrigin = 'http://localhost:5173';
        }

        return [
            'storageDir' => $realStorageDir,
            'logDir' => $realLogDir,
            'allowedOrigin' => $allowedOrigin,
        ];
    }
}
