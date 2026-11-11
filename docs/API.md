# API Reference

The application exposes PHP JSON endpoints under `public/api/` for the built-in server. The repository-level `api/` directory contains equivalent entry points for direct Apache deployments.

## Endpoint summary

| Endpoint | Method | Purpose | Main response key |
|---|---|---|---|
| `/api/auth.php` | `GET`, `POST` | Session status and authentication | `authenticated`, `user` |
| `/api/inventory.php` | `GET` | Inventory products and stock | `products` |
| `/api/purchases.php` | `GET` | Purchase orders | `orders` |
| `/api/sales.php` | `GET` | Sales records | `sales` |
| `/api/requisitions.php` | `GET` | Requisitions | `requisitions` |
| `/api/reports.php` | `GET` | Report records | `reports` |
| `/api/accounting.php` | `GET` | Accounting summary data | Module-specific accounting keys |
| `/api/gps.php` | `GET` | Installation/GPS device records | `devices` |

## URL examples

With the PHP built-in server:

```bash
curl http://127.0.0.1:8000/api/inventory.php
curl http://127.0.0.1:8000/api/reports.php
curl http://127.0.0.1:8000/api/gps.php
```

## Response format

Successful endpoints return JSON containing `ok: true` and a resource-specific key. For example:

```json
{
  "ok": true,
  "products": []
}
```

Failures return an HTTP error status and a safe JSON error message:

```json
{
  "ok": false,
  "error": "Unable to load inventory"
}
```

## Authentication endpoint

`GET /api/auth.php` reports the current session and returns a CSRF token for an authenticated session. Authentication requests use `POST` and must include the expected CSRF token. Invalid credentials return HTTP 401; invalid tokens return HTTP 403; unsupported methods return HTTP 405.

## Security guidance

Protect API entry points with the same web-server and application access controls as the main application. Use HTTPS in production, do not expose database credentials, and do not log passwords, reset tokens, or personal information. The API is intended for the application’s operational integrations, not as an unrestricted public data feed.
