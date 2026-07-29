# iTrack Zimbabwe ERP

This workspace now includes a working MVC-based PHP scaffold for an ERP-style application with:

- authentication flow for login/logout
- dashboard entry point
- inventory module with MVC controller/model/view
- database schema and seed SQL for MySQL

## Setup

1. Create a MySQL database named `itrack_zimbabwe`.
2. Import [database/schema.sql](database/schema.sql) and [database/seed.sql](database/seed.sql).
3. Ensure the application can reach the database in [app/config/app.php](app/config/app.php).
4. Open [public/login.php](public/login.php) in your web server.

## Default login

- Email: admin@itrack.local
- Password: password
