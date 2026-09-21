# ANAPEC Chercheurs API - Phase 1, 2 & 3

## Architecture

Laravel 12 + Oracle (OCI8) + Sanctum token authentication.

### Database Tables

| Table | Owner | Purpose |
|-------|-------|---------|
| `api_users` | ANAPEC2 | API user accounts (role: admin/user) |
| `api_web_services` | ANAPEC2 | Web service definitions (code, name, is_active) |
| `api_user_web_services` | ANAPEC2 | Pivot: user <-> web service (is_enabled) |
| `personal_access_tokens` | ANAPEC2 | Sanctum tokens |

### Roles

- **admin**: Full access to user management and web service permissions
- **user**: Can authenticate and consume enabled web services only

## Authentication

All API routes are under `/api/v1`.

### Login

```
POST /api/v1/auth/login
```

Request:
```json
{
  "email": "admin@example.com",
  "password": "secret123"
}
```

Response (200):
```json
{
  "success": true,
  "message": "Authenticated.",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin",
      "email": "admin@example.com",
      "role": "admin",
      "is_active": true,
      "created_at": "2026-09-17T10:00:00+00:00",
      "updated_at": "2026-09-17T10:00:00+00:00"
    },
    "token": "abc123...",
    "token_type": "Bearer"
  }
}
```

Rate limit: 10 attempts per minute.

### Logout

```
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

Response (200):
```json
{
  "success": true,
  "message": "Logged out."
}
```

### Current User

```
GET /api/v1/auth/me
Authorization: Bearer {token}
```

Response (200):
```json
{
  "success": true,
  "message": "Authenticated user.",
  "data": {
    "id": 1,
    "name": "Admin",
    "email": "admin@example.com",
    "role": "admin",
    "is_active": true,
    "created_at": "...",
    "updated_at": "..."
  }
}
```

## Admin Endpoints

All require `Authorization: Bearer {token}` with an admin user.

### List Users

```
GET /api/v1/admin/users
```

### Create User

```
POST /api/v1/admin/users
```

Request:
```json
{
  "name": "New User",
  "email": "user@example.com",
  "password": "secret123",
  "role": "user",
  "is_active": true
}
```

### Show User

```
GET /api/v1/admin/users/{id}
```

### Update User

```
PUT /api/v1/admin/users/{id}
```

Request:
```json
{
  "name": "Updated Name",
  "email": "updated@example.com",
  "password": "newpassword1",
  "role": "admin"
}
```

### Toggle User Status

```
PATCH /api/v1/admin/users/{id}/status
```

Request:
```json
{
  "is_active": false
}
```

### Reset User Password

```
POST /api/v1/admin/users/{id}/password
```

Request:
```json
{
  "password": "newpassword1"
}
```

### List User Web Services

```
GET /api/v1/admin/users/{id}/web-services
```

Response (200):
```json
{
  "success": true,
  "message": "Web Services retrieved.",
  "data": [
    {
      "code": "WS_CV",
      "name": "CV",
      "global_is_active": true,
      "is_enabled": true,
      "effective_access": true
    }
  ]
}
```

### Assign User Web Services

```
PUT /api/v1/admin/users/{id}/web-services
```

Request:
```json
{
  "web_services": [
    {"code": "WS_CHECK_CIN", "enabled": true},
    {"code": "WS_PROFILE", "enabled": false}
  ]
}
```

## Web Service Management (Phase 2)

### List Web Services

```
GET /api/v1/admin/web-services
```

Response (200):
```json
{
  "success": true,
  "message": "Web Services retrieved.",
  "data": [
    {
      "id": 1,
      "code": "WS_CHECK_CIN",
      "name": "Check CIN",
      "description": "Verify a CIN number",
      "is_active": true,
      "created_at": "...",
      "updated_at": "..."
    }
  ]
}
```

### Show Web Service

```
GET /api/v1/admin/web-services/{code}
```

### Toggle Web Service Status (Global ON/OFF)

```
PATCH /api/v1/admin/web-services/{code}/status
```

Request:
```json
{
  "is_active": false
}
```

Response (200):
```json
{
  "success": true,
  "message": "Web Service status updated successfully.",
  "data": {
    "code": "WS_CV",
    "is_active": false
  }
}
```

When a Web Service is globally disabled, ALL users receive HTTP 403 when
attempting to consume it, even if their individual permission is still enabled.
User permission records are NOT deleted or modified when the global status
changes. Re-enabling the service restores access for users whose permission
remains enabled.

## Web Service Permission Check

Use middleware `EnsureUserCanConsumeWebService` with route parameter:

```php
Route::middleware(['auth:api', 'ws:WS_CV'])->get('/cv', [CVController::class, 'index']);
```

Permission is valid when:
1. User is authenticated
2. User is active
3. Web service exists and is active
4. User has a permission record
5. Permission `is_enabled = true`

Otherwise returns HTTP 403.

## User Self-Service

### Reset Own Password

```
PATCH /api/v1/user/password
Authorization: Bearer {token}
```

Request:
```json
{
  "current_password": "oldpassword1",
  "password": "newpassword1",
  "password_confirmation": "newpassword1"
}
```

## Creating Admin Users

```bash
php artisan app:create-api-user --role=admin --email=admin@example.com --name="Admin" --password="secret123"
```

## Tests

```bash
php artisan test
```

Tests run against the live Oracle database using `DatabaseTransactions`.

## Web Service Consumption (Phase 3)

### Check CIN Existence

```
POST /api/v1/services/check-cin
Authorization: Bearer {token}
```

Requires: WS_CHECK_CIN permission enabled + WS_CHECK_CIN globally active + user active.

Request:
```json
{
  "cin": "EA154824"
}
```

Response (200, exists):
```json
{
  "success": true,
  "message": "CIN check completed.",
  "data": {
    "exists": true
  }
}
```

Response (200, not found):
```json
{
  "success": true,
  "message": "CIN check completed.",
  "data": {
    "exists": false
  }
}
```

Rate limit: 60 requests/minute.

See `docs/services/WS_CHECK_CIN.md` for full details.

## Next Phase

- Web service consumption endpoints (Profile, CV, Bilan)

## Swagger / OpenAPI

Swagger UI is available at:

| URL | Description |
|-----|-------------|
| `http://127.0.0.1:8000/api/documentation` | Swagger UI |
| `http://127.0.0.1:8000/api/documentation/openapi` | OpenAPI JSON spec |

### Usage

1. Start Laravel:
   ```bash
   php artisan serve
   ```

2. Open Swagger UI in your browser:
   ```
   http://127.0.0.1:8000/api/documentation
   ```

3. **Authenticate**:
   - Click the **Authorize** button (top-right)
   - Enter your Sanctum Bearer token (from `POST /api/v1/auth/login`)

4. **Test WS_CHECK_CIN**:
   - Navigate to `POST /services/check-cin`
   - Click **Try it out**
   - Enter a CIN in the request body:
     ```json
     { "cin": "EA154824" }
     ```
   - Click **Execute**
   - Inspect the response (`exists: true` or `exists: false`)

### OpenAPI Spec

The spec is maintained at `storage/openapi/openapi.yaml`.
Served as JSON at `/api/documentation/openapi` (no conversion at runtime —
the YAML is parsed with Symfony Yaml and returned as JSON).
