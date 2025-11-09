# CodingAssessmentJ202509084

## Prerequisites(For details please see PrerequisitesSetup.md)

- PHP 8.1+, Composer
- Node 18+
- Xero Developer account with a **Custom Connection** attached to a **Demo Company**

## Configure env

- `cd api-php && cp .env.example .env`
- Then fillin the ID and key generated from your Xero app.

## Run Server & UI

```bash
cd api-php && composer install
php -S localhost:8080 -t public

cd web-react && npm install
npm run dev
```

Open `http://localhost:5173` -> click **Test Connection** -> **Sync Accounts** / **Sync Vendors**.

## API Documentation(For details please see APIDoc.md)

API documentation is using Swagger UI:
- Start the PHP server: `cd api-php && php -S localhost:8080 -t public`
- Open: `http://localhost:8080/docs`
- OpenAPI JSON spec: `http://localhost:8080/api-docs.json`

## Testing(For details please see UnitTestsREADME.md)

Run PHP unit tests:

```bash
cd api-php && php -S localhost:8080 -t public
```
- Now open another terminal and run:
``` 
composer test
```

## Where files go

- JSON/CSV are written to `api-php/public/storage/`.
- Logs at `api-php/public/logs/app.log`.

## High Level Design Diagram

<img width="1465" height="814" alt="Architecture   High Level Design" src="https://github.com/user-attachments/assets/88ebf41a-d25f-4837-88c1-1b5272e0380d" />

