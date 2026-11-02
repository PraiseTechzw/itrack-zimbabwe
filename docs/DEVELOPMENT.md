# Development Guide

## Project structure

- [`app/config`](../app/config) – environment, application, database, and route configuration
- [`app/controllers`](../app/controllers) – request handlers for each module
- [`app/models`](../app/models) – data access and business logic
- [`app/views`](../app/views) – server-rendered presentation templates
- [`app/core`](../app/core) – router, controller, model, database, session, and security components
- [`app/helpers`](../app/helpers) – authentication, permissions, validation, formatting, uploads, notifications, and responses
- [`app/services`](../app/services) – report formatting, PDF/XLSX exports, and optional insights
- [`public`](../public) – browser entry points and public API copies
- [`database`](../database) – schema, seed data, migrations, and backup guidance
- [`storage`](../storage) – writable runtime files and generated exports
- [`uploads`](../uploads) – uploaded documents and media
- [`docs/screenshots`](screenshots/README.md) – current visual captures

## MVC workflow

The project uses a lightweight MVC pattern with plain PHP classes.

When adding a feature:

1. Add or update the controller in `app/controllers/`.
2. Add or update the model in `app/models/` when persistence is required.
3. Add the view in `app/views/`.
4. Add the controller action to `app/config/routes.php` when it is a routed action.
5. Add or update middleware and permission checks.
6. Update the public API only when the data contract is intentional.
7. Update the relevant documentation and screenshot index.

## Local workflow

```bash
mkdir -p storage uploads
php -S 127.0.0.1:8000 -t public
```

Use the built-in server for local iteration. Keep `.env` untracked and use a local SQLite database for isolated development.

## Verification

Syntax-check PHP files:

```bash
find app api public includes tests -type f -name '*.php' -print0 | xargs -0 -n1 php -l
```

Run the lightweight notification helper check against a configured database:

```bash
php tests/notification_service_test.php
```

GitHub Actions runs the PHP syntax check on pushes to `main` and on pull requests. The workflow is defined in [`.github/workflows/php.yml`](../.github/workflows/php.yml) and currently validates the project with PHP 8.2.

Manual smoke checks should cover authentication, role-aware navigation, CRUD actions, bulk inventory, installations, accounting, report previews/exports, APIs, and password recovery. Capture updated screens with a clean browser viewport and update [`docs/screenshots/README.md`](screenshots/README.md).

## Coding and security conventions

- Use prepared PDO statements through the existing model/database helpers.
- Keep CSRF validation on state-changing requests.
- Validate and sanitize server-side even when the view has HTML constraints.
- Escape output in views.
- Keep secrets in `.env` or the server environment, never in committed code.
- Keep generated exports, sessions, uploads, and database files outside the public document root.
