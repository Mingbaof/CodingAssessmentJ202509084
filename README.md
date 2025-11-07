# CodingAssessmentJ202509084

## Prerequisites(For details please see PrerequisitesSetup.md)

- PHP 8.1+, Composer
- Node 18+
- Xero Developer account with a **Custom Connection** attached to a **Demo Company**

## Configure env

- Put it in `/api-php/.env`

## Run

```bash
cd api-php && composer install
php -S localhost:8080 -t public

cd web-react && npm install
npm run dev
```

Open `http://localhost:5173` → click **Test Connection** → **Sync Accounts** / **Sync Vendors**.

## Where files go

- JSON/CSV are written to `api-php/public/storage/`.
- Logs at `api-php/public/logs/app.log`.

## Notes

- For Custom Connections, the **tenant is implicit**; pass an empty string for the `$xeroTenantId` parameter with the PHP SDK.
- The server obtains access tokens via `client_credentials`; no refresh token is used. We will request a new token when needed.

# TODO:

- Need to move logic to use hooks in UI
- Need some simple unit tests
- Need to improve logging
- Need to test error handling edge cases
