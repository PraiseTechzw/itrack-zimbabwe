# Security Guide

## Reporting a vulnerability

Please do not open a public issue for a vulnerability. Use the repository’s [private GitHub Security Advisory form](https://github.com/PraiseTechzw/itrack-zimbabwe/security/advisories/new) when available, or contact the repository maintainers through a private channel.

Include the affected version or commit, the affected route or component, reproduction steps, impact, and a suggested mitigation when possible. Remove credentials, personal data, customer records, API keys, reset tokens, and production URLs from the report. Please allow maintainers reasonable time to investigate and prepare a fix before public disclosure.

## Application security expectations

- Keep PHP, the web server, database server, and dependencies updated.
- Use HTTPS in production and set `APP_SESSION_SECURE=true`.
- Keep `APP_DEBUG=false` in production.
- Use strong unique passwords and rotate the seeded administrator password immediately.
- Disable public registration in production unless it is intentionally required.
- Protect password-reset tokens, API keys, database credentials, logs, exports, backups, and uploaded files.
- Restrict write access to `storage/` and `uploads/` without exposing the repository root to the web.
- Preserve CSRF validation, server-side validation, output escaping, prepared PDO statements, authentication middleware, and permission checks.
- Do not place real customer data or production credentials in issues, pull requests, screenshots, fixtures, or tests.

## Deployment review

Before production handover, verify the public document root is [`public/`](../public), sensitive files are not directly served, database credentials are outside source control, backups are protected, error logs are monitored, and the default administrator account has been secured.
