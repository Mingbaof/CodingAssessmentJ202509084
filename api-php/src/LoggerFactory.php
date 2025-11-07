<?php
namespace App;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

// Logger class to create a logger instance
class LoggerFactory
{
    public static function create(string $logDir, string $name = 'app'): Logger
    {
        $logger = new Logger($name);
        $handler = new StreamHandler(rtrim($logDir,'/').'/app.log', Logger::INFO);
        $handler->setFormatter(new LineFormatter(null, null, true, true));
        $logger->pushHandler($handler);
        return $logger;
    }
}
