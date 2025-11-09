# Test Files Overview

## Test Environment Setup

- **Unit Tests:** Use temporary directories and mocked components
- **Integration Tests:** Require PHP development server running on `localhost:8080`
- **Environment Tests:** Isolated environment variable testing with cleanup

### ApiEndpointsTest.php
**Purpose:** Tests individual API endpoint functionality with mocked components and isolated environment.

**Test Cases:**
- `testHealthEndpoint()` - Verifies `/health` endpoint returns correct JSON response
- `testDocsEndpoint()` - Validates `/docs` endpoint serves HTML documentation
- `testApiDocsJsonEndpoint()` - Checks `/api-docs.json` endpoint returns valid OpenAPI specification
- `testAuthStatusEndpointConfigured()` - Tests `/auth/status` when Xero credentials are configured
- `testAuthStatusEndpointNotConfigured()` - Tests `/auth/status` when credentials are missing
- `testSyncAccountsEndpoint()` - Validates `/sync/accounts` POST endpoint functionality with mock data
- `testSyncVendorsEndpoint()` - Validates `/sync/vendors` POST endpoint functionality with mock data
- `testNotFoundEndpoint()` - Ensures 404 response for non-existent routes

### ApiIntegrationTest.php
**Purpose:** End-to-end integration tests that make actual HTTP requests to running API server.

**Test Cases:**
- `testHealthEndpoint()` - Live test of health check endpoint
- `testDocsEndpoint()` - Live test of documentation page serving
- `testApiDocsJsonEndpoint()` - Live test of OpenAPI JSON specification endpoint
- `testAuthStatusEndpoint()` - Live test of authentication status endpoint
- `testSyncAccountsEndpoint()` - Live test of accounts synchronization (handles both success/error cases)
- `testSyncVendorsEndpoint()` - Live test of vendors synchronization (handles both success/error cases)
- `testNotFoundEndpoint()` - Live test of 404 handling
- `testOptionsMethod()` - Tests CORS preflight OPTIONS requests

### EnvironmentTest.php
**Purpose:** Tests environment variable handling and configuration management.

**Test Cases:**
- `testGetEnvironmentVariableFromEnv()` - Verifies reading variables from `$_ENV`
- `testGetEnvironmentVariableWithDefault()` - Tests fallback to default values for missing variables
- `testEnvironmentVariablePriority()` - Ensures `$_ENV` takes priority over `$_SERVER`

## Running Tests

```bash
cd api-php && php -S localhost:8080 -t public
```
- Now open another terminal and run:
``` 
composer test
```
