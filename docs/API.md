# ANAPEC Chercheurs API - Phase 1

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

### List Web Services

```
GET /api/v1/admin/web-services
```

### Show Web Service

```
GET /api/v1/admin/web-services/{code}
```

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

## Next Phase

- Web service consumption endpoints (CV, Profile, CIN, Bilan)
- ON/OFF toggle for web services at runtime
