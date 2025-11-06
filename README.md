# CodingAssessmentJ202509084

## Prerequisites(For details please see PrerequisitesSetup.md)

- PHP 8.1+, Composer
- Node 18+
- Xero Developer account with a **Custom Connection** attached to a **Demo Company**

## Configure env

- It's in `/api-php/.env`

## Run

```bash
cd app/api-php && cp .env.example .env && composer install
php -S localhost:8080 -t public

cd ../../app/web-react && npm install
npm run dev
```

Open `http://localhost:5173` → click **Test Connection** → **Sync Accounts** / **Sync Vendors**.

## Where files go

- JSON/CSV are written to `app/api-php/storage/`.
- Logs at `app/api-php/logs/app.log`.

## Notes

- For Custom Connections, the **tenant is implicit**; pass an empty string for the `$xeroTenantId` parameter with the PHP SDK.
- The server obtains access tokens via `client_credentials`; no refresh token is used — request a new token when needed.
