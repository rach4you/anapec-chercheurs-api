# ANAPEC Chercheurs API

## Project

Internal API application for ANAPEC.

The application exposes Web Services consumed by other applications, including the
existing WhatsApp registration application and SIGEC.

## Stack

- Laravel 12
- PHP 8.3 (x64 NTS)
- Oracle Database
- OCI8 (PHP extension)
- Yajra Laravel OCI8
- Composer
- Git
- GitHub

## Purpose

The application will provide Web Services for:

1. Chercheur existence verification via CIN
2. Chercheur profile creation/update
3. CV PDF upload
4. Bilan de compétences creation/update

## Database

Oracle.

Oracle connectivity is provided through the PHP OCI8 extension and the
`yajra/laravel-oci8` package. The database connection uses the standard Laravel
`oracle` connection driver registered by Yajra.

## Isolated environment (separate from legacy applications)

This project is developed against a **dedicated, isolated** PHP and Oracle
client environment that is separate from the legacy PHP 8.1 / 32-bit Oracle
Instant Client installation used by other existing applications. The legacy
environment is intentionally left untouched.

- PHP 8.3 x64 NTS lives in `C:\php83`.
- Oracle Instant Client 19.x (64-bit) lives in `C:\instantclient_19_64`.
- The OCI8 extension is loaded from the `C:\php83` PHP installation only.

When running any command for this project, ensure the 64-bit Oracle client
directory is on the `PATH` so the OCI8 extension can locate the Oracle
runtime libraries.

## Development

Required tooling:

- PHP 8.3 (x64 NTS) — a separate PHP 8.3 installation, not the legacy PHP 8.1
- Composer
- Oracle Instant Client 19.x (64-bit) — a separate 64-bit client installation
- PHP OCI8 extension (compatible with PHP 8.3)
- Laravel 12
- Yajra Laravel OCI8

Setup steps:

1. Install PHP 8.3 x64 NTS and configure its `php.ini` to load the OCI8
   extension and enable the extensions required by Composer/Laravel.
2. Install a 64-bit Oracle Instant Client (19.x or later).
3. Make sure the Oracle client directory is on the `PATH` when running
   commands for this project.
4. From the project root, install dependencies:
   ```bash
   C:\php83\php.exe vendor\bin\composer install
   ```
   (or use the Composer launcher with PHP 8.3 on the `PATH`)
5. Configure the Oracle connection in `.env` (see below).
6. Copy `.env.example` to `.env` and fill in the Oracle connection values.

## Environment

Configure Oracle credentials in `.env`. Example (values must be provided
locally, never committed):

```env
DB_CONNECTION=oracle
DB_HOST=
DB_PORT=1521
DB_DATABASE=
DB_SERVICE_NAME=
DB_USERNAME=
DB_PASSWORD=
```

`.env` contains the real credentials and is ignored by Git. `.env.example`
holds only the variable structure with no real values.

**Never commit `.env`**, passwords, API keys, Oracle credentials, tokens,
certificates, or any secrets.

## Current Status

Phase 0 — Project initialization.

- Laravel 12 project created
- Yajra Laravel OCI8 installed
- Oracle connection configured
- Isolated PHP 8.3 / 64-bit Oracle environment in place

## Next Phase

Authentication and Security.
