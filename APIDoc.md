# API Documentation

#### Files:
- `api-docs.json` - OpenAPI specification
- `public/docs.html` - Swagger UI interface
- Access via: `http://localhost:8080/api-docs.json`

## Accessing Documentation

1. **Start the server:**
   ```bash
   cd api-php && php -S localhost:8080 -t public
   ```

2. **View documentation:**
   - **Swagger UI**: `http://localhost:8080/docs`
   - **OpenAPI JSON**: `http://localhost:8080/api-docs.json`
   - **Health check**: `http://localhost:8080/health`
   - **Test endpoints**: Use the "Try it out" buttons in Swagger UI

## Current API Endpoints

### Health
- `GET /health` - Health check

### Authentication  
- `GET /auth/status` - Check Xero connection status

### Data Sync
- `POST /sync/accounts` - Sync accounts from Xero
- `POST /sync/vendors` - Sync vendors from Xero

### **Current File Structure**
```
api-php/
└── public/
    ├── openapi.json # OpenAPI specification
    ├── docs.html # Swagger UI interface  
    └── index.php # Main API + documentation routes
```
