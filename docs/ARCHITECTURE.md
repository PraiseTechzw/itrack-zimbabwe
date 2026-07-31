# Architecture Overview

## Request flow

1. A browser request enters through one of the files in [public](../public).
2. The request is routed through the application router or directly to a controller.
3. The controller coordinates with models and helpers.
4. The selected view is rendered and returned to the browser.

## Core components

- [app/core/Controller.php](../app/core/Controller.php) – base controller functionality
- [app/core/Model.php](../app/core/Model.php) – base model behavior
- [app/core/Router.php](../app/core/Router.php) – request routing logic
- [app/core/Database.php](../app/core/Database.php) – database connection and initialization
- [app/helpers](../app/helpers) – reusable helper functions such as auth, validation, and response formatting

## Data layer

The project uses PDO for database access. The database layer can connect to MySQL or fall back to SQLite automatically when MySQL support is unavailable.

## File storage

Uploaded files and runtime assets are stored under [uploads](../uploads) and [storage](../storage). These directories must be writable by the web server.
