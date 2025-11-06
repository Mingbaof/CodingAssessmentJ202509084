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
            $dotenv->load();
        }
        $storageDir = Environment::get('STORAGE_DIR') ?: __DIR__.'/../storage';
        $logDir = Environment::get('LOG_DIR') ?: __DIR__.'/../logs';
        if (!is_dir($storageDir)) mkdir($storageDir, 0777, true);
        if (!is_dir($logDir)) mkdir($logDir, 0777, true);

        return [
            'storageDir' => realpath($storageDir) ?: $storageDir,
            'logDir' => realpath($logDir) ?: $logDir,
            'allowedOrigin' => Environment::get('ALLOWED_ORIGIN') ?: 'http://localhost:5173',
        ];
    }
}
