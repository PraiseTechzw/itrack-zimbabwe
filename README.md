# iTrack Zimbabwe ERP

This repository contains a PHP MVC scaffold for an ERP-style application with modules for authentication, dashboard access, inventory, purchases, sales, accounting, requisitions, reports, and GPS-related workflows.

## Features

- Authentication flow for login and logout
- Dashboard entry point
- Inventory module with controller, model, and view structure
- Database schema and seed SQL for MySQL and SQLite fallback support
- Upload directories and runtime storage for documents and reports

## Running locally

### Prerequisites

- PHP 8.1 or newer
- A local web server, or PHP's built-in server

### Quick start

1. Clone the repository.
2. Make sure the writable directories are available to the web server:
   - [storage](storage)
   - [uploads](uploads)
3. Start the app locally:

```bash
php -S 127.0.0.1:8000 -t public
```

4. Open the application in your browser:

```text
http://127.0.0.1:8000/login.php
```

## Database setup

The application can use MySQL or SQLite.

### MySQL

1. Create a database named `itrack_zimbabwe`.
2. Update the connection values in [app/config/app.php](app/config/app.php).
3. Import [database/schema.sql](database/schema.sql) and [database/seed.sql](database/seed.sql).

### SQLite fallback

If PDO MySQL is not available, the app will create a local SQLite database in [storage/itrack.sqlite](storage/itrack.sqlite) on first run.

## Default login

- Email: `admin@example.com`
- Password: `password`

## Documentation

The full documentation set is available in [docs](docs):

- [docs/INSTALLATION.md](docs/INSTALLATION.md)
- [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md)
- [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)
- [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)
- [docs/SECURITY.md](docs/SECURITY.md)

## Contributing

Contributions are welcome. Please open an issue or pull request with a clear summary and testing notes.
