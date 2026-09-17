# WS_CHECK_CIN - CIN Existence Verification

## Purpose

Verify whether a job seeker (chercheur) exists in the SIGEC Oracle database
using their CIN (Carte d'Identité Nationale) number.

## Endpoint

```
POST /api/v1/services/check-cin
Authorization: Bearer {sanctum-token}
```

## Request

```json
{
  "cin": "EA154824"
}
```

## Authentication & Authorization

- Requires Sanctum authentication
- User must be **active**
- User must have **WS_CHECK_CIN** permission enabled
- WS_CHECK_CIN must be **globally active**
- Rate limited: 60 requests/minute

## Response

### Exists

```json
{
  "success": true,
  "message": "CIN check completed.",
  "data": {
    "exists": true
  }
}
```

### Not Found

```json
{
  "success": true,
  "message": "CIN check completed.",
  "data": {
    "exists": false
  }
}
```

### Validation Error (422)

```json
{
  "success": false,
  "message": "The cin field is required.",
  "errors": {
    "cin": ["The cin field is required."]
  }
}
```

### Unauthorized (401/403)

```json
{
  "success": false,
  "message": "..."
}
```

## Oracle Source

| Property | Value |
|----------|-------|
| **Table** | `ANAPEC2.CHERCHEURS` |
| **CIN Column** | `CIN` (VARCHAR2(15), NOT NULL) |
| **Index** | Bitmap index on CIN |
| **Row Count** | ~3.4M |
| **Query** | `SELECT 1 FROM CHERCHEURS WHERE CIN = :cin AND ROWNUM = 1` |

## Query Strategy

- Uses Laravel's query builder with parameter binding
- Generates an `EXISTS` subquery via `DB::table('chercheurs')->where('cin', $cin)->exists()`
- Read-only: only `SELECT` is issued
- No full-table scan: leverages the existing bitmap index on `CIN`

## Normalization

- CIN is trimmed of surrounding whitespace before the query
- Comparison is case-sensitive (Oracle default for VARCHAR2)
- No silent alteration of the CIN value

## Security

- Parameterized query (no SQL concatenation)
- No Oracle error details exposed to the client
- No researcher personal data returned (existence only)
- No tokens or passwords in the response

## Limitations

- Only checks existence in the `CHERCHEURS` table
- Does not verify CIN validity (format, check digit, etc.)
- Does not return any researcher details (future services will handle that)
