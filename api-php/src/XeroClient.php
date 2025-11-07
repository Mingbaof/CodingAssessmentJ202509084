<?php
namespace App;

use League\OAuth2\Client\Provider\GenericProvider;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Configuration;
use Psr\Log\LoggerInterface;

class XeroClient
{
    private GenericProvider $provider;
    private Storage $storage;
    private LoggerInterface $logger;
    private string $tokenPath;

    public function __construct(Storage $storage, LoggerInterface $logger)
    {
        $this->provider = new GenericProvider([
            'clientId'                => Environment::get('XERO_CLIENT_ID'),
            'clientSecret'            => Environment::get('XERO_CLIENT_SECRET'),
            'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
            'urlAccessToken'          => 'https://identity.xero.com/connect/token',
            'urlResourceOwnerDetails' => 'https://identity.xero.com/resources'
        ]);
        $this->storage = $storage;
        $this->logger = $logger;
        $this->tokenPath = $this->storage->path('tokens.json');
    }

    public function isConfigured(): bool
    {
        return (bool) Environment::get('XERO_CLIENT_ID') && (bool) Environment::get('XERO_CLIENT_SECRET');
    }

    public function getToken(): array
    {
        $t = $this->loadToken();
        $now = time();
        if (!$t or (($t['expires'] ?? 0) <= $now + 30)) {
            $this->logger->info('Fetching new client_credentials access token');
            $options = [];
            $scopes = trim((string) Environment::get('XERO_SCOPES'));
            if ($scopes !== '') {
                $options['scope'] = $scopes;
            }
            
            try {
                $accessToken = $this->provider->getAccessToken('client_credentials', $options);
                $t = [
                    'access_token' => $accessToken->getToken(),
                    'expires' => $accessToken->getExpires(),
                ];
                $this->persistToken($t);
                $this->logger->info('Successfully obtained access token');
            } catch (\Exception $e) {
                $this->logger->error('Failed to get access token: ' . $e->getMessage());
                $this->logger->error('Exception type: ' . get_class($e));
                if ($e->getPrevious()) {
                    $this->logger->error('Previous exception: ' . $e->getPrevious()->getMessage());
                }
                throw $e;
            }
        }
        return $t;
    }

    public function getAccountingApi(): AccountingApi
    {
        $token = $this->getToken();
        $config = Configuration::getDefaultConfiguration()->setAccessToken($token['access_token']);
        return new AccountingApi(null, $config);
    }

    public function getAccounts(): array
    {
        $api = $this->getAccountingApi();
        $result = $api->getAccounts(''); // tenant id implicit in Custom Connection
        
        // $this->logger->info('Raw Xero Accounts Response', [
        //     'type' => get_class($result),
        //     'data' => print_r($result, true)
        // ]);
        
        $rows = [];
        foreach ($result->getAccounts() as $a) {
            $rows[] = [
                'AccountID'   => (string)$a->getAccountID(),
                'Code'        => (string)$a->getCode(),
                'Name'        => (string)$a->getName(),
                'Type'        => (string)$a->getType(),
                'Class'       => (string)$a->getClass(),
                'Status'      => (string)$a->getStatus(),
                'EnablePaymentsToAccount' => (bool)$a->getEnablePaymentsToAccount(),
            ];
        }
        return $rows;
    }

    public function getVendors(): array
    {
        $api = $this->getAccountingApi();
        $where = 'IsSupplier==true';
        $result = $api->getContacts('', null, $where);

        // $this->logger->info('Raw Xero Vendors Response', [
        //     'type' => get_class($result),
        //     'data' => print_r($result, true)
        // ]);
        
        $rows = [];
        foreach ($result->getContacts() as $c) {
            $rows[] = [
                'ContactID'    => (string)$c->getContactID(),
                'Name'         => (string)$c->getName(),
                'EmailAddress' => (string)$c->getEmailAddress(),
                'IsSupplier'   => (bool)$c->getIsSupplier(),
                'IsCustomer'   => (bool)$c->getIsCustomer(),
                'ContactStatus'=> (string)$c->getContactStatus(),
            ];
        }
        return $rows;
    }

    private function persistToken(array $t): void
    {
        file_put_contents($this->tokenPath, json_encode($t, JSON_PRETTY_PRINT));
    }

    private function loadToken(): ?array
    {
        if (!file_exists($this->tokenPath)) {
            return null;
        }
        $raw = file_get_contents($this->tokenPath);
        if ($raw) {
            return json_decode($raw, true);
        } else {
            return null;
        }
    }
}
