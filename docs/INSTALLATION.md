# Installation Guide

## Requirements

- PHP 8.1 or newer.
- PDO SQLite for local fallback or PDO MySQL for MySQL deployments.
- Apache/Nginx for production or PHP's built-in server for local development.
- Writable `storage/` and `uploads/` directories.
- Composer is optional because the current project has no required third-party runtime packages.

## Quick start

```bash
git clone https://github.com/PraiseTechzw/itrack-zimbabwe.git
cd itrack-zimbabwe
mkdir -p storage uploads
chmod -R u+rwX storage uploads
php -S 127.0.0.1:8000 -t public
```

Open `http://127.0.0.1:8000/login.php`.

The application initializes `storage/itrack.sqlite` when SQLite is selected or when the default automatic database mode cannot connect to MySQL.

## Environment configuration

Create a root `.env` file for local overrides. A typical local configuration is:

```dotenv
APP_ENV=development
APP_DEBUG=false
APP_BASE_URL=/
APP_ALLOW_REGISTRATION=true
APP_SESSION_SECURE=false
DB_DRIVER=sqlite
DB_SQLITE_PATH=/absolute/path/to/storage/itrack.sqlite
UPLOAD_DIR=/absolute/path/to/uploads
```

For production, set `APP_ENV=production`, configure `DB_DRIVER=mysql` and the `DB_*` values, set `APP_SESSION_SECURE=true` behind HTTPS, and keep `APP_ALLOW_REGISTRATION=false` unless open registration is intentional.

## Database setup

### MySQL

1. Create the `itrack_zimbabwe` database.
2. Set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS`.
3. Import [`database/schema.sql`](../database/schema.sql).
4. Import [`database/seed.sql`](../database/seed.sql).
5. Apply relevant files in [`database/migrations/`](../database/migrations/) to existing installations in order.

### SQLite

SQLite is created automatically on first application access. The local default account can be created from the seed data or by the application’s local setup process.

## Default credentials

The seeded local administrator is:

- Email: `admin@example.com`
- Password: `password`

Change this password immediately outside local development.

## Public directory

The web-server document root must be [`public/`](../public), not the repository root. Public entry points include `login.php`, `dashboard.php`, `logout.php`, `index.php`, and the API directory.

## Troubleshooting

- **Blank or fatal database errors:** confirm the PDO extension and database variables.
- **Uploads or exports fail:** check write permission on `uploads/` and `storage/`.
- **Links point to the wrong path:** set `APP_BASE_URL` to the deployment mount path.
- **Registration is unavailable:** confirm `APP_ALLOW_REGISTRATION=true` and that the environment is not production-only.
- **Password reset is not delivered:** configure `APP_PUBLIC_URL`, `MAIL_FROM`, and the server mail transport.
