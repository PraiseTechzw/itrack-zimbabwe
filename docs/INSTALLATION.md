# Installation Guide

## Requirements

- PHP 8.1 or newer
- A web server such as Apache or Nginx, or PHP's built-in server for local development
- Optional: MySQL 8.0+ for production-style database usage
- Optional: Composer if you plan to manage PHP dependencies

## Quick start

1. Clone the repository and enter the project folder.
2. Make the writable folders available to the web server:
   - storage/
   - uploads/
3. Start the application locally:

```bash
php -S 127.0.0.1:8000 -t public
```

4. Open the login page in your browser:

```text
http://127.0.0.1:8000/login.php
```

## Database setup

The application can run with either MySQL or SQLite.

### Option 1: MySQL

1. Create a database named `itrack_zimbabwe`.
2. Update the database connection values in [app/config/app.php](../app/config/app.php).
3. Import the SQL files from [database/schema.sql](../database/schema.sql) and [database/seed.sql](../database/seed.sql).

### Option 2: SQLite fallback

If PDO MySQL is unavailable, the application automatically creates a local SQLite database in [storage/itrack.sqlite](../storage/itrack.sqlite) on first run.

## Default credentials

The seeded local user is:

- Email: `admin@example.com`
- Password: `password`

## Notes

- The project base URL is configured as `/itrack-zimbabwe` in [app/config/app.php](../app/config/app.php). Update it if your local environment uses a different path.
- The public entry points are located under [public](../public).
