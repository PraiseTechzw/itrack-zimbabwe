# iTrack Zimbabwe

[![PHP 8.1+](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![SQLite / MySQL](https://img.shields.io/badge/Database-SQLite%20%7C%20MySQL-4F46E5?style=for-the-badge&logo=sqlite&logoColor=white)](docs/INSTALLATION.md)
[![Documentation](https://img.shields.io/badge/Docs-current-14B8A6?style=for-the-badge&logo=readthedocs&logoColor=white)](docs/README.md)
[![License: MIT](https://img.shields.io/badge/License-MIT-64748B?style=for-the-badge)](LICENSE)

**iTrack Zimbabwe** is a lightweight PHP 8.1+ ERP-style operations platform for managing inventory, tracker installations, procurement, sales, accounting, requisitions, reporting, notifications, and users.

The application uses a small MVC structure built with plain PHP classes and PDO. It can run locally with SQLite and can be deployed with MySQL behind Apache, Nginx, or another PHP-capable web server.

![iTrack Zimbabwe product tour](docs/assets/product-tour.gif)

> A focused operations workspace for inventory, installations, teams, finance, and reporting.

## Current functionality

### Authentication and access control

- Login and logout with session-based authentication.
- Optional staff registration, controlled by `APP_ALLOW_REGISTRATION`.
- Forgot-password and expiring password-reset-token flows.
- Authenticated password changes.
- CSRF protection, server-side validation, input sanitisation, and permission checks.
- Role-aware navigation and module access.
- Configurable role/module permission matrix.

### Operations modules

- **Dashboard** – role-aware workspace with operational summaries and available-module access.
- **Inventory** – stock records for trackers, FLS units, cameras, SIM cards/mobile lines, and related equipment; quantities, cost prices, selling prices, units, and optional numeric identifiers such as serial numbers, SIM numbers, or IMEIs.
- **Bulk inventory** – create multiple stock lines in one submission, with validation for serialized items.
- **Installations / GPS devices** – record tracker installations, tracker identifiers, and optional FLS, camera, and package equipment selected from available stock. Stock quantities are reduced when equipment is assigned.
- **Bulk installations** – record several installations in one workflow.
- **Clients** – customer and client organization records.
- **Suppliers** – procurement partner records.
- **Purchases** – purchase-order capture and status updates with supplier workflows and notifications.
- **Sales** – sales capture and status visibility.
- **Requisitions** – internal requests with approval and decision workflows.
- **Notifications** – create, edit, delete, list, and mark notifications as read.
- **Users** – staff accounts, roles, departments, and account status management.
- **Settings** – application administration and configuration access.

### Accounting and reporting

- **Accounting summary** with access controlled for finance users.
- **Invoices** with create, edit, delete, and print views.
- **Payments**, **expenses**, **petty cash**, and **cash book** records with CRUD workflows.
- **Reports** with operational and financial report types, preset periods, custom date ranges, previews, generated records, and export support.
- **Report exports** through the PDF and XLSX report services.
- Optional OpenRouter-powered business insights using bounded report aggregates. The assistant does not execute AI-generated SQL or receive customer names.

## Requirements

- PHP 8.1 or newer.
- PDO SQLite for the local fallback, or PDO MySQL for MySQL deployments.
- A web server such as Apache or Nginx for production, or PHP's built-in server for local development.
- A writable `storage/` directory for the SQLite database, logs, and runtime files.
- A writable `uploads/` directory when upload features are enabled.
- Composer is optional; the project currently has no third-party runtime dependency requirement.

## Quick start

From the repository root:

```bash
git clone https://github.com/PraiseTechzw/itrack-zimbabwe.git
cd itrack-zimbabwe
php -S 127.0.0.1:8000 -t public
```

Open [http://127.0.0.1:8000/login.php](http://127.0.0.1:8000/login.php).

On Windows with XAMPP, use the PHP executable supplied by XAMPP:

```powershell
C:\xampp\php\php.exe -S 127.0.0.1:8000 -t public
```

Make sure the runtime directories exist and are writable by the PHP process:

```bash
mkdir -p storage uploads
chmod -R u+rwX storage uploads
```

The application creates `storage/itrack.sqlite` automatically when SQLite is selected or when PDO MySQL is unavailable.

## Configuration

The application reads an optional `.env` file from the repository root. Environment variables can also be supplied by the server process.

```dotenv
APP_NAME="iTrack Zimbabwe"
APP_ENV=development
APP_DEBUG=false
APP_BASE_URL=/itrack-zimbabwe
APP_PUBLIC_URL=
APP_ALLOW_REGISTRATION=true
APP_SESSION_SECURE=false
SESSION_NAME=itrack_session
CSRF_TOKEN_NAME=csrf_token

# Use mysql explicitly for production, or auto for the default behaviour.
DB_DRIVER=auto
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=itrack_zimbabwe
DB_USER=root
DB_PASS=
DB_SQLITE_PATH=/absolute/path/to/storage/itrack.sqlite

UPLOAD_DIR=/absolute/path/to/uploads
MAIL_FROM=no-reply@example.com

# Optional Reports assistant
OPENROUTER_API_KEY=
OPENROUTER_MODEL=openrouter/free
```

Important configuration notes:

- Set `APP_BASE_URL` to the path where the application is mounted. Use `/` when it is served at the domain root.
- Set `APP_PUBLIC_URL` when generated links must use a complete public URL.
- Set `APP_SESSION_SECURE=true` when the application is served over HTTPS.
- Disable public registration in production unless it is intentionally required.
- Keep database credentials and API keys out of source control.
- `DB_DRIVER=mysql` selects MySQL explicitly. The default `auto` mode uses the available PDO driver and falls back to SQLite when MySQL is unavailable.

## Default local login

The seeded local account is:

- Email: `admin@example.com`
- Password: `password`

Change this password immediately outside a local development environment.

## Current application screenshots

Every image below was captured from the current running application after the latest build was started. Desktop screens use a 1440 × 1000 viewport and the mobile dashboard uses a 390 × 844 viewport. The screenshots contain no production data. The registration screen is captured with `APP_ALLOW_REGISTRATION=true` for development documentation; production deployments should keep registration disabled unless intentionally required.

The animated product tour above provides a quick visual overview; the complete capture set is also indexed in [docs/screenshots/README.md](docs/screenshots/README.md).

<details open>
<summary><strong>Public and authentication</strong></summary>

<table>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/login.png"><img src="docs/screenshots/login.png" alt="Login" width="440"></a><br><sub>Login</sub></td>
<td valign="top" width="50%"><a href="docs/screenshots/register.png"><img src="docs/screenshots/register.png" alt="Registration" width="440"></a><br><sub>Registration</sub></td>
</tr>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/forgot-password.png"><img src="docs/screenshots/forgot-password.png" alt="Forgot password" width="440"></a><br><sub>Forgot password</sub></td>
<td valign="top" width="50%"><a href="docs/screenshots/reset-password.png"><img src="docs/screenshots/reset-password.png" alt="Reset password" width="440"></a><br><sub>Reset password</sub></td>
</tr>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/change-password.png"><img src="docs/screenshots/change-password.png" alt="Change password" width="440"></a><br><sub>Change password</sub></td>
</tr>
</table>

</details>

<details open>
<summary><strong>Workspace and administration</strong></summary>

<table>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/dashboard.png"><img src="docs/screenshots/dashboard.png" alt="Dashboard" width="440"></a><br><sub>Dashboard</sub></td>
<td valign="top" width="50%"><a href="docs/screenshots/dashboard-mobile.png"><img src="docs/screenshots/dashboard-mobile.png" alt="Dashboard mobile" width="440"></a><br><sub>Dashboard mobile</sub></td>
</tr>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/users.png"><img src="docs/screenshots/users.png" alt="Users" width="440"></a><br><sub>Users</sub></td>
<td valign="top" width="50%"><a href="docs/screenshots/users-form.png"><img src="docs/screenshots/users-form.png" alt="User form" width="440"></a><br><sub>User form</sub></td>
</tr>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/permissions.png"><img src="docs/screenshots/permissions.png" alt="Permissions" width="440"></a><br><sub>Permissions</sub></td>
<td valign="top" width="50%"><a href="docs/screenshots/settings.png"><img src="docs/screenshots/settings.png" alt="Settings" width="440"></a><br><sub>Settings</sub></td>
</tr>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/notifications.png"><img src="docs/screenshots/notifications.png" alt="Notifications" width="440"></a><br><sub>Notifications</sub></td>
</tr>
</table>

</details>

<details open>
<summary><strong>Inventory and installations</strong></summary>

<table>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/inventory.png"><img src="docs/screenshots/inventory.png" alt="Inventory" width="440"></a><br><sub>Inventory</sub></td>
<td valign="top" width="50%"><a href="docs/screenshots/inventory-form.png"><img src="docs/screenshots/inventory-form.png" alt="Inventory form" width="440"></a><br><sub>Inventory form</sub></td>
</tr>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/inventory-bulk.png"><img src="docs/screenshots/inventory-bulk.png" alt="Bulk inventory" width="440"></a><br><sub>Bulk inventory</sub></td>
<td valign="top" width="50%"><a href="docs/screenshots/gps.png"><img src="docs/screenshots/gps.png" alt="Installations / GPS" width="440"></a><br><sub>Installations / GPS</sub></td>
</tr>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/gps-form.png"><img src="docs/screenshots/gps-form.png" alt="Installation form" width="440"></a><br><sub>Installation form</sub></td>
<td valign="top" width="50%"><a href="docs/screenshots/gps-bulk.png"><img src="docs/screenshots/gps-bulk.png" alt="Bulk installations" width="440"></a><br><sub>Bulk installations</sub></td>
</tr>
</table>

</details>

<details open>
<summary><strong>Clients and suppliers</strong></summary>

<table>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/clients.png"><img src="docs/screenshots/clients.png" alt="Clients" width="440"></a><br><sub>Clients</sub></td>
<td valign="top" width="50%"><a href="docs/screenshots/clients-form.png"><img src="docs/screenshots/clients-form.png" alt="Client form" width="440"></a><br><sub>Client form</sub></td>
</tr>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/suppliers.png"><img src="docs/screenshots/suppliers.png" alt="Suppliers" width="440"></a><br><sub>Suppliers</sub></td>
<td valign="top" width="50%"><a href="docs/screenshots/suppliers-form.png"><img src="docs/screenshots/suppliers-form.png" alt="Supplier form" width="440"></a><br><sub>Supplier form</sub></td>
</tr>
</table>

</details>

<details open>
<summary><strong>Procurement and commercial operations</strong></summary>

<table>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/purchases.png"><img src="docs/screenshots/purchases.png" alt="Purchases" width="440"></a><br><sub>Purchases</sub></td>
<td valign="top" width="50%"><a href="docs/screenshots/sales.png"><img src="docs/screenshots/sales.png" alt="Sales" width="440"></a><br><sub>Sales</sub></td>
</tr>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/requisitions.png"><img src="docs/screenshots/requisitions.png" alt="Requisitions" width="440"></a><br><sub>Requisitions</sub></td>
</tr>
</table>

</details>

<details open>
<summary><strong>Accounting and reporting</strong></summary>

<table>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/accounting.png"><img src="docs/screenshots/accounting.png" alt="Accounting summary" width="440"></a><br><sub>Accounting summary</sub></td>
<td valign="top" width="50%"><a href="docs/screenshots/accounting-invoices.png"><img src="docs/screenshots/accounting-invoices.png" alt="Invoices" width="440"></a><br><sub>Invoices</sub></td>
</tr>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/accounting-invoice-form.png"><img src="docs/screenshots/accounting-invoice-form.png" alt="Invoice form" width="440"></a><br><sub>Invoice form</sub></td>
<td valign="top" width="50%"><a href="docs/screenshots/accounting-payments.png"><img src="docs/screenshots/accounting-payments.png" alt="Payments" width="440"></a><br><sub>Payments</sub></td>
</tr>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/accounting-expenses.png"><img src="docs/screenshots/accounting-expenses.png" alt="Expenses" width="440"></a><br><sub>Expenses</sub></td>
<td valign="top" width="50%"><a href="docs/screenshots/accounting-petty-cash.png"><img src="docs/screenshots/accounting-petty-cash.png" alt="Petty cash" width="440"></a><br><sub>Petty cash</sub></td>
</tr>
<tr>
<td valign="top" width="50%"><a href="docs/screenshots/accounting-cash-book.png"><img src="docs/screenshots/accounting-cash-book.png" alt="Cash book" width="440"></a><br><sub>Cash book</sub></td>
<td valign="top" width="50%"><a href="docs/screenshots/reports.png"><img src="docs/screenshots/reports.png" alt="Reports" width="440"></a><br><sub>Reports</sub></td>
</tr>
</table>

</details>

## MySQL setup

For a new MySQL database:

1. Create a database named `itrack_zimbabwe`.
2. Configure the `DB_*` variables in `.env`.
3. Import [`database/schema.sql`](database/schema.sql).
4. Import [`database/seed.sql`](database/seed.sql).
5. Apply any required scripts in [`database/migrations/`](database/migrations/) to an existing database before deploying code that depends on those changes.

Important migrations cover inventory device details, IMEIs, SIM numbers, password-reset tokens, stock-backed installations and invoices, and the mobile-line/SIM-card model update.

## Application routes

Authenticated modules are routed through `public/index.php` using `controller` and `action` query parameters. Examples:

```text
/index.php?controller=inventory
/index.php?controller=gps
/index.php?controller=accounting&action=invoices
/index.php?controller=purchases
/index.php?controller=sales
/index.php?controller=requisition
/index.php?controller=reports
/index.php?controller=notification
/index.php?controller=permissions
/index.php?controller=settings
```

Public entry points are available under [`public/`](public/), including the login, logout, dashboard, and API entry points. The repository-level [`api/`](api/) copies remain available for Apache deployments that expose them directly.

## JSON API

The read-oriented API endpoints return JSON with an `ok` flag and module-specific data. The authentication endpoint also supports login and session checks.

| Endpoint | Purpose |
|---|---|
| `/api/auth.php` | Authentication and session status |
| `/api/inventory.php` | Inventory products and stock records |
| `/api/purchases.php` | Purchase orders |
| `/api/sales.php` | Sales records |
| `/api/requisitions.php` | Requisitions |
| `/api/reports.php` | Reports |
| `/api/accounting.php` | Accounting data |
| `/api/gps.php` | Tracker installation / GPS device records |

When using the built-in server, use the corresponding paths under `public/api/`, for example `http://127.0.0.1:8000/api/inventory.php`.

## Project structure

```text
app/
  config/        Environment, application, database, and route configuration
  controllers/   Request handlers for each module
  core/          Router, controller, model, database, session, and security classes
  helpers/       Authentication, permissions, validation, formatting, uploads, and responses
  middleware/    Authentication, administrator, and permission middleware
  models/        Database models and domain operations
  services/      Report formatting, PDF/XLSX generation, and optional AI insights
  views/         Server-rendered PHP views
public/          Web document root and public entry points
api/             API entry points for direct Apache deployments
database/        Schema, seed data, migrations, and backup guidance
docs/            Installation, development, architecture, deployment, and security guides
tests/           Lightweight verification scripts
storage/         Writable runtime data, including the SQLite database
uploads/         Writable uploaded documents and media
```

See the [architecture guide](docs/ARCHITECTURE.md) for request flow and core components, the [feature guide](docs/FEATURES.md) for module behavior, the [API reference](docs/API.md) for integrations, and the [development guide](docs/DEVELOPMENT.md) for the recommended MVC workflow.

## Development and verification

Useful checks from the repository root:

```bash
# Syntax-check all PHP source files
find app api public includes tests -type f -name '*.php' -print0 | xargs -0 -n1 php -l

# Run the lightweight notification helper check
php tests/notification_service_test.php
```

The project currently relies primarily on PHP syntax checks, module smoke tests, and manual browser verification. The notification test expects a working configured database because it creates a notification record.

Before opening a pull request, verify login, role-based navigation, CRUD actions, bulk inventory, installations, accounting workflows, report previews/exports, API responses, and password recovery in the target environment.

## Contributing

Please read the [contribution guide](.github/CONTRIBUTING.md), [code of conduct](.github/CODE_OF_CONDUCT.md), and [security guide](docs/SECURITY.md) before opening an issue or pull request. UI changes should include sanitized screenshots, database changes should include migrations and rollback notes, and security issues should be reported privately rather than through public issues.

## Deployment and security

For production deployments:

- Point the web-server document root at [`public/`](public/), not the repository root.
- Enable PHP and `pdo_mysql` when using MySQL.
- Use HTTPS and set `APP_SESSION_SECURE=true`.
- Keep `storage/` and `uploads/` writable but prevent source files and secrets from being served publicly.
- Rotate the default administrator password and use strong individual passwords.
- Restrict database, log, upload, and backup permissions.
- Keep PHP, the web server, and database server updated.

Read the [installation guide](docs/INSTALLATION.md), [deployment guide](docs/DEPLOYMENT.md), and [security guide](docs/SECURITY.md) for additional guidance. See the [changelog](CHANGELOG.md) for release history. Report security issues privately to the repository maintainer rather than opening a public issue.

## License

iTrack Zimbabwe is released under the [MIT License](LICENSE). Copyright © 2026 PraiseTechzw. The license permits commercial and private use, modification, distribution, and sublicensing, subject to preserving the copyright and permission notice.
