<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use App\Bootstrap;
use App\LoggerFactory;
use App\Storage;
use App\XeroClient;
use App\Environment;

[$config] = [Bootstrap::init()];
$logger = LoggerFactory::create($config['logDir']);
$storage = new Storage($config['storageDir']);
$xero = new XeroClient($storage, $logger);

// Make variables available in $GLOBALS
$GLOBALS['storage'] = $storage;
$GLOBALS['xero'] = $xero;

$origin = $config['allowedOrigin'];

function cors($origin) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
}

cors($origin);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function json($data, int $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

try {
    if ($path === '/health') {
        json(['ok' => true]);
    }

    if ($path === '/auth/status' && $_SERVER['REQUEST_METHOD']==='GET') {
        $configured = $GLOBALS['xero']->isConfigured();
        $connected = false;
        $error = null;
        $missingConfig = [];
        
        // Check for missing configuration
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
                $error = 'Missing required environment variables: ' . implode(', ', $missingConfig) . '. Please create a .env file in the api-php directory with these variables.';
            }
        } else {
            try {
                $GLOBALS['xero']->getToken();
                $connected = true;
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }
        
        json(['configured' => $configured, 'connected' => $connected, 'error' => $error]);
    }

    if ($path === '/sync/accounts' && $_SERVER['REQUEST_METHOD']==='POST') {
        $rows = $GLOBALS['xero']->getAccounts();
        $GLOBALS['storage']->writeJson('accounts.json', $rows);
        $GLOBALS['storage']->writeCsv('accounts.csv', $rows);
        json(['count' => count($rows), 'rows' => $rows]);
    }

    if ($path === '/sync/vendors' && $_SERVER['REQUEST_METHOD']==='POST') {
        $rows = $GLOBALS['xero']->getVendors();
        $GLOBALS['storage']->writeJson('vendors.json', $rows);
        $GLOBALS['storage']->writeCsv('vendors.csv', $rows);
        json(['count' => count($rows), 'rows' => $rows]);
    }

    # no routes matched
    http_response_code(404);
    echo 'Not Found';
} catch (Throwable $e) {
    $GLOBALS['logger']->error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
    json(['error' => $e->getMessage()], 500);
}
