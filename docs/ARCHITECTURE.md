# Architecture Overview

## Request flow

1. A browser request enters through one of the public files in [`public/`](../public).
2. `public/index.php` reads the `controller` and `action` query parameters, loads the matching controller class, and dispatches the action. Requests without explicit controller parameters can be resolved by [`app/core/Router.php`](../app/core/Router.php).
3. Authentication, administrator, and module-permission middleware guard protected actions.
4. The controller validates input, coordinates models/helpers/services, and selects a view.
5. The view renders the response through the shared layout and navigation components.
6. JSON requests use the API entry points under `public/api/` or the repository-level `api/` copies for direct deployments.

## Core components

- [`app/core/Controller.php`](../app/core/Controller.php) – shared redirects, views, sessions, CSRF fields, and request helpers
- [`app/core/Model.php`](../app/core/Model.php) – shared PDO model behavior
- [`app/core/Router.php`](../app/core/Router.php) – path-based request resolution
- [`app/core/Database.php`](../app/core/Database.php) – MySQL connection, SQLite initialization, and schema helpers
- [`app/core/Security.php`](../app/core/Security.php) – security-related shared behavior
- [`app/helpers/`](../app/helpers) – authentication, permissions, validation, formatting, uploads, notifications, mail, and responses
- [`app/middleware/`](../app/middleware) – authentication, administrator, and permission enforcement
- [`app/services/`](../app/services) – reports, PDF/XLSX exports, and optional business insights

## Module structure

Each major module generally has a controller, one or more models, and one or more views. Module access is represented in the permission model and reflected in role-aware navigation. State-changing actions should validate CSRF tokens, sanitize and validate inputs, use prepared statements, and redirect after successful writes.

## Data layer

The project uses PDO for database access. `DB_DRIVER=mysql` uses the configured MySQL connection. SQLite is initialized automatically for local development when selected explicitly or when automatic mode cannot connect to MySQL outside production. MySQL installations use [`database/schema.sql`](../database/schema.sql), [`database/seed.sql`](../database/seed.sql), and versioned scripts in [`database/migrations/`](../database/migrations/).

## File storage

Uploaded files and runtime assets are stored under `uploads/` and `storage/`. These directories must be writable by the PHP process but should not be directly browsable. Generated exports, session files, logs, backups, and local databases are runtime data and must not be committed.

## Extension points

A new module should add its controller/model/view pieces, permission definition, route/action, API contract if needed, migration/seed changes, tests or smoke checks, and documentation. UI changes should also update the current screenshot index without including real data.
