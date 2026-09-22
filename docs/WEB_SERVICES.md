# Web Services - Authorization Model

## Global Status

`api_web_services.is_active`

- Controls whether the Web Service is available to ANY user.
- Toggled by ADMIN via `PATCH /api/v1/admin/web-services/{code}/status`.
- Web Services are registered by the developer through the seeder;
  the admin does NOT create new Web Services.
- When globally OFF, ALL users receive HTTP 403 regardless of their individual
  permission records.
- When globally ON, consumption still requires the user's individual permission
  to be enabled.

## User Permission

`api_user_web_services.is_enabled`

- Controls whether a specific user is allowed to consume a Web Service.
- Managed by ADMIN via:
  - `PUT /api/v1/admin/users/{id}/web-services` — bulk/full replacement of the
    whole assigned set (`min:1`, inactive services rejected).
  - `PATCH /api/v1/admin/users/{id}/web-services/{code}` — enable/disable one
    permission (Phase 4.2).
  - `DELETE /api/v1/admin/users/{id}/web-services` — revoke every permission
    for the user at once (Phase 4.2).
- Independent of the global status.

## Effective Access

A user can consume a Web Service only when ALL three conditions are met:

```
user_active AND service_active AND permission_enabled
```

| user_active | service_active | permission_enabled | Result |
|-------------|----------------|--------------------|--------|
| true        | true           | true               | ALLOW  |
| true        | true           | false              | 403    |
| true        | false          | true               | 403    |
| false       | true           | true               | 403    |
| false       | false          | true               | 403    |

## HTTP 403 Behavior

The middleware `EnsureUserCanConsumeWebService` returns 403 with a generic
message when any condition fails:

- "Unauthenticated." — no token or invalid token
- "Account is deactivated." — user `is_active` = false
- "Unknown Web Service." — code not found in `api_web_services`
- "Web Service is disabled." — `api_web_services.is_active` = false
- "Insufficient permissions." — no enabled permission record

## Global OFF Does Not Delete Permissions

When an admin turns a Web Service globally OFF:

- `api_user_web_services` rows are NOT modified or deleted.
- User permission records remain `is_enabled = true`.
- When the admin turns the service back ON, users whose permission was
  enabled can consume it again immediately without re-assignment.

## Initial Web Services

| Code | Name | Description |
|------|------|-------------|
| WS_CHECK_CIN | Check CIN | Verify a CIN number |
| WS_PROFILE | Profile | Return a researcher profile |
| WS_CV | CV | Return a researcher CV |
| WS_BILAN | Bilan | Return a researcher bilan |
