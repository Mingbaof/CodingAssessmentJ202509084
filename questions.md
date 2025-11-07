# Interview Preparation - 20 Mock Questions & Answers

This document contains 20 mock interview questions with detailed answers based on the coding assessment implementation. These questions are tailored for a junior software developer position.

## Table of Contents

1. [Architecture & Design Questions](#architecture--design-questions)
2. [React & Frontend Questions](#react--frontend-questions)
3. [PHP & Backend Questions](#php--backend-questions)
4. [Error Handling & Logging Questions](#error-handling--logging-questions)
5. [Testing & Quality Questions](#testing--quality-questions)
6. [Security Questions](#security-questions)
7. [Performance & Optimization Questions](#performance--optimization-questions)
8. [Integration & API Questions](#integration--api-questions)
9. [Code Quality & Maintenance Questions](#code-quality--maintenance-questions)

---

## Architecture & Design Questions

### 1. Can you walk me through the overall architecture of your application?

**Answer:** This is a full-stack application with a clear separation of concerns:

- **Frontend:** React application using Vite as the build tool, with custom hooks for state management
- **Backend:** PHP API using plain PHP (no framework) with a simple routing system
- **Integration:** Connects to Xero API using OAuth2 client credentials flow
- **Storage:** File-based storage for JSON and CSV data in the `api-php/public/storage/` directory
- **Logging:** Monolog for structured logging in PHP

The frontend communicates with the backend via REST API endpoints, and the backend handles authentication with Xero and data synchronization.

### 2. Why did you choose to use custom hooks in React instead of a state management library like Redux?

**Answer:** For this application's scope, custom hooks provide several advantages:

- **Simplicity:** The app has straightforward state needs (auth status, sync data, messages)
- **Separation of concerns:** Each hook handles a specific domain (`useAuth`, `useSync`, `useMessage`)
- **Testability:** Custom hooks are easily mockable for unit testing
- **Performance:** No unnecessary re-renders from global state changes
- **Bundle size:** No additional dependencies required

The hooks pattern also makes the code more readable and follows React best practices.

---

## React & Frontend Questions

### 3. Explain how the `useSync` hook works and why you used useCallback.

**Answer:** The `useSync` hook manages data synchronization with the backend:

```javascript
const sync = useCallback(async (path, setter) => {
  setLoading(true);
  try {
    const res = await fetch(`${API}${path}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
    });
    const j = await res.json();
    const rows = j.rows || [];
    setter(rows);
    return { count: j.count ?? rows.length };
  } finally {
    setLoading(false);
  }
}, []);
```

I used `useCallback` to:
- **Prevent unnecessary re-renders:** Without it, the function would be recreated on every render
- **Dependency optimization:** Child components using this function won't re-render unnecessarily
- **Memory optimization:** Avoids creating new function instances

### 4. How do you handle error states in your React application?

**Answer:** I implement error handling at multiple levels:

- **Try-catch blocks:** In async operations to catch and handle exceptions
- **Error messages:** Using the `useMessage` hook to display user-friendly error messages
- **UI state:** Disabling buttons when not connected, showing appropriate status messages
- **Graceful degradation:** Showing "No data" when arrays are empty instead of crashing

Example in the sync handler:
```javascript
try {
  const result = await syncFunction();
  showMessage(`Synced ${result.count} ${type} records`, false);
} catch (error) {
  showMessage(`Error: ${error.message}`, false);
}
```

### 5. What testing strategy did you implement for the React components?

**Answer:** I used Vitest with React Testing Library for component testing:

- **Mock dependencies:** All hooks are mocked to control test scenarios
- **User interaction testing:** Testing button clicks and user workflows
- **State management testing:** Verifying different auth/connection states
- **Error scenario testing:** Testing disabled states and error conditions

Key testing patterns:
- Mocking external dependencies
- Testing user interactions rather than implementation details
- Verifying accessibility and user experience

---

## PHP & Backend Questions

### 6. Explain your PHP API routing approach and why you didn't use a framework.

**Answer:** I implemented a simple routing system in `index.php`:

```php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/auth/status' && $_SERVER['REQUEST_METHOD']==='GET') {
    // Handle auth status
}
if ($path === '/sync/accounts' && $_SERVER['REQUEST_METHOD']==='POST') {
    // Handle account sync
}
```

**Why no framework:**
- **Simplicity:** For a small API with few endpoints, a framework would be overkill
- **Performance:** No framework overhead
- **Learning opportunity:** Demonstrates understanding of HTTP fundamentals
- **Dependencies:** Minimal external dependencies

However, for larger applications, I'd use Laravel or Symfony for better structure and features.

### 7. How does your OAuth2 implementation work with Xero?

**Answer:** I use the client credentials flow, which is appropriate for server-to-server communication:

```php
public function getToken(): array
{
    $t = $this->loadToken();
    $now = time();
    if (!$t or (($t['expires'] ?? 0) <= $now + 30)) {
        $accessToken = $this->provider->getAccessToken('client_credentials', $options);
        $t = [
            'access_token' => $accessToken->getToken(),
            'expires' => $accessToken->getExpires(),
        ];
        $this->persistToken($t);
    }
    return $t;
}
```

**Key features:**
- **Token caching:** Tokens are persisted to avoid unnecessary API calls
- **Automatic renewal:** Tokens are renewed 30 seconds before expiration
- **Error handling:** Comprehensive logging for debugging OAuth issues
- **Custom connections:** Uses empty tenant ID for Xero custom connections

### 8. Explain your file storage approach and its limitations.

**Answer:** I implemented a simple file-based storage system:

- **JSON files:** For structured data that can be easily parsed
- **CSV files:** For human-readable exports and Excel compatibility
- **Token storage:** Secure token persistence between requests

**Limitations:**
- **Concurrency:** No file locking, potential race conditions
- **Scalability:** Won't handle high traffic or large datasets
- **Backup:** No built-in backup or redundancy
- **Security:** Files are accessible if directory permissions are misconfigured

**For production, I'd use:**
- Database storage (MySQL/PostgreSQL)
- Redis for caching
- Proper file permissions and encryption

---

## Error Handling & Logging Questions

### 9. How do you handle errors in your PHP application?

**Answer:** I implement comprehensive error handling:

```php
try {
    // API operations
} catch (Throwable $e) {
    $GLOBALS['logger']->error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
    json(['error' => $e->getMessage()], 500);
}
```

**Error handling strategy:**
- **Global exception handler:** Catches all uncaught exceptions
- **Structured logging:** Using Monolog with context and stack traces
- **User-friendly responses:** Convert technical errors to user-friendly messages
- **HTTP status codes:** Proper status codes (404, 500, etc.)
- **CORS handling:** Proper CORS for cross-origin requests

### 10. What logging strategy did you implement and why?

**Answer:** I use Monolog for structured logging:

- **Structured format:** JSON-formatted logs with context
- **Multiple levels:** Info for normal operations, Error for exceptions
- **Context inclusion:** Stack traces and relevant data included
- **File-based:** Logs written to `api-php/public/logs/app.log`

**Benefits:**
- **Debugging:** Easy to trace issues in production
- **Monitoring:** Can be integrated with log aggregation tools
- **Audit trail:** Track API usage and data access
- **Performance:** Log timing and performance metrics

---

## Testing & Quality Questions

### 11. What types of tests did you write and why?

**Answer:** I implemented both PHP and React tests:

**PHP Tests (PHPUnit):**
- **Unit tests:** Testing `Environment` class functionality
- **Configuration testing:** Environment variable handling
- **Priority testing:** Ensuring `$_ENV` takes precedence over `$_SERVER`

**React Tests (Vitest):**
- **Component tests:** Testing App component behavior
- **Hook mocking:** Isolating component logic from hook implementation
- **User interaction:** Testing button clicks and state changes
- **Error scenarios:** Testing disabled states and error handling

**Coverage areas:**
- Happy path scenarios
- Error conditions
- Edge cases
- User interactions

### 12. How would you improve the testing coverage?

**Answer:** Several areas need improvement:

- **PHP API endpoints:** Integration tests for each endpoint
- **Xero client testing:** Mock Xero API responses
- **Storage class testing:** File I/O operations
- **E2E testing:** Full user workflow testing
- **Error scenarios:** Network failures, invalid tokens
- **Performance testing:** Load testing for sync operations
- **Security testing:** Input validation, authentication

I'd also add:
- Test data fixtures
- Database seeding for consistent test states
- CI/CD pipeline integration
- Code coverage reporting

---

## Security Questions

### 13. What security considerations did you implement?

**Answer:** Several security measures are in place:

**CORS Configuration:**
```php
function cors($origin) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
}
```

**Environment Variables:** Sensitive data (API keys) stored in `.env`
**Token Security:** OAuth tokens have expiration and are securely stored
**Input Validation:** JSON responses prevent code injection
**Session Management:** PHP sessions for state management

**Additional security needed:**
- Input sanitization and validation
- Rate limiting
- HTTPS enforcement
- File permission restrictions
- SQL injection prevention (when using databases)

### 14. How do you protect against common vulnerabilities?

**Answer:** Current protections:

- **XSS:** JSON responses and React's built-in XSS protection
- **CSRF:** CORS configuration limits request origins
- **Injection:** No direct database queries, using prepared statements equivalent in Xero SDK
- **Authentication:** OAuth2 with token expiration

**Additional protection needed:**
- **Rate limiting:** Prevent API abuse
- **Input validation:** Sanitize all user inputs
- **HTTPS only:** Encrypt data in transit
- **Security headers:** CSP, HSTS, X-Frame-Options
- **File upload restrictions:** If implemented
- **Dependency scanning:** Regular security updates

---

## Performance & Optimization Questions

### 15. How did you optimize the performance of your application?

**Answer:** Several optimization strategies:

**Frontend:**
- **React hooks optimization:** Using `useCallback` to prevent unnecessary re-renders
- **Conditional rendering:** Only showing data when available
- **Efficient state updates:** Minimal state management with targeted updates

**Backend:**
- **Token caching:** Avoiding unnecessary OAuth requests
- **Efficient data mapping:** Direct field mapping without unnecessary processing
- **File-based storage:** Fast reads/writes for small datasets

**API Design:**
- **Single-purpose endpoints:** Each endpoint has one responsibility
- **Batch operations:** Sync all accounts/vendors in one request
- **Proper HTTP methods:** GET for status, POST for sync operations

### 16. What would you do to handle larger datasets?

**Answer:** For scalability improvements:

**Database Implementation:**
- Replace file storage with PostgreSQL/MySQL
- Implement pagination for large datasets
- Add database indexing for performance

**Caching Strategy:**
- Redis for frequently accessed data
- HTTP caching headers
- Application-level caching

**API Optimization:**
- Implement pagination (`?page=1&limit=50`)
- Add filtering and search capabilities
- Background job processing for large syncs
- API response compression

**Frontend Optimization:**
- Virtual scrolling for large tables
- Lazy loading and pagination
- Debounced search inputs
- Progressive data loading

---

## Integration & API Questions

### 17. Explain how the Xero API integration works.

**Answer:** The integration uses Xero's official PHP SDK:

**Authentication Flow:**
```php
$this->provider = new GenericProvider([
    'clientId' => Environment::get('XERO_CLIENT_ID'),
    'clientSecret' => Environment::get('XERO_CLIENT_SECRET'),
    'urlAuthorize' => 'https://login.xero.com/identity/connect/authorize',
    'urlAccessToken' => 'https://identity.xero.com/connect/token',
]);
```

**Data Retrieval:**
- **Accounts:** Using `getAccounts()` with empty tenant ID for custom connections
- **Vendors:** Using `getContacts()` with `IsSupplier==true` filter
- **Data mapping:** Converting Xero objects to arrays for JSON serialization

**Key features:**
- Automatic token management
- Error handling and logging
- Custom connection support (implicit tenant)

### 18. How would you handle API rate limiting?

**Answer:** Xero API rate limiting strategies:

**Current approach:** Basic error handling catches rate limit exceptions

**Improved approach:**
```php
// Implement exponential backoff
private function makeRequest($callable, $maxRetries = 3) {
    $attempt = 0;
    while ($attempt < $maxRetries) {
        try {
            return $callable();
        } catch (RateLimitException $e) {
            $delay = pow(2, $attempt) * 1000; // Exponential backoff
            usleep($delay * 1000); // Convert to microseconds
            $attempt++;
        }
    }
    throw new Exception('Max retries exceeded');
}
```

**Additional strategies:**
- Implement request queuing
- Cache frequently requested data
- Monitor rate limit headers
- Implement circuit breaker pattern

---

## Code Quality & Maintenance Questions

### 19. How do you ensure code quality and maintainability?

**Answer:** Several practices implemented:

**Code Organization:**
- **Clear separation:** Frontend/backend separation
- **Single responsibility:** Each class/hook has one purpose
- **Naming conventions:** Descriptive variable and function names

**Error Handling:**
- Comprehensive try-catch blocks
- Structured logging with context
- User-friendly error messages

**Testing:**
- Unit tests for critical functionality
- Component tests with mocking
- Error scenario testing

**Documentation:**
- Clear README with setup instructions
- Inline code comments for complex logic
- API endpoint documentation in code

**Improvements needed:**
- Code formatting standards (PSR-12 for PHP, Prettier for JS)
- Static analysis tools (PHPStan, ESLint)
- Automated code review tools
- More comprehensive documentation

### 20. What would you do differently if you started this project again?

**Answer:** Several improvements I would make:

**Architecture:**
- **Use a PHP framework:** Laravel or Symfony for better structure
- **Database storage:** PostgreSQL instead of file-based storage
- **API versioning:** Proper versioning strategy (/api/v1/)
- **Docker containerization:** For consistent development environments

**Security:**
- **Input validation library:** Comprehensive validation
- **Security middleware:** Authentication, rate limiting, CORS
- **Environment-specific configs:** Separate dev/staging/prod configurations

**Testing:**
- **Test-driven development:** Write tests first
- **Integration tests:** Full API endpoint testing
- **E2E testing:** Cypress or Playwright for user workflows
- **CI/CD pipeline:** Automated testing and deployment

**Code Quality:**
- **Linting and formatting:** ESLint, Prettier, PHPStan
- **Code coverage:** Minimum coverage requirements
- **Pre-commit hooks:** Ensure code quality before commits
- **API documentation:** OpenAPI/Swagger documentation

**Monitoring:**
- **Application monitoring:** Error tracking (Sentry)
- **Performance monitoring:** APM tools
- **Health checks:** Comprehensive health endpoints
- **Metrics and alerting:** Monitor API usage and errors

These improvements would make the application more production-ready and maintainable for a team environment.

---

## Tips for the Interview

1. **Be prepared to explain your code:** Know every line and be able to justify design decisions
2. **Discuss trade-offs:** Acknowledge limitations and explain why you made certain choices
3. **Show learning mindset:** Demonstrate how you would improve the code
4. **Ask questions:** Show interest in their tech stack and development practices
5. **Be honest:** If you don't know something, say so and explain how you would find out

Good luck with your interview! 🚀
