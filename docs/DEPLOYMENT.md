# Deployment Guide

## Production checklist

- Point the web-server document root at [`public/`](../public), not the repository root.
- Run PHP 8.1+ with `pdo_mysql` enabled for MySQL deployments.
- Create and secure a production `.env` outside version control.
- Set `APP_ENV=production`, `APP_DEBUG=false`, and `APP_SESSION_SECURE=true` behind HTTPS.
- Configure `APP_BASE_URL` and `APP_PUBLIC_URL` for the deployed host.
- Set `DB_DRIVER=mysql` and valid `DB_*` credentials.
- Import the schema, seed data, and required migrations.
- Make `storage/` and `uploads/` writable by the PHP process, but not publicly browsable.
- Disable public registration unless intentionally required.
- Enable HTTPS, backups, log rotation, and database access restrictions.
- Rotate the default administrator password before handover.

## Apache

Configure the virtual host `DocumentRoot` to the project `public` directory and allow PHP execution there. Keep the project root, `.env`, database files, logs, and uploads outside the publicly served directory. Enable the required PHP modules and restart Apache after configuration changes.

## Nginx with PHP-FPM

Use the `public` directory as the site root and pass `.php` requests to PHP-FPM. A minimal shape is:

```nginx
server {
    listen 443 ssl;
    server_name example.com;
    root /var/www/itrack-zimbabwe/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
}
```

Adjust the PHP-FPM socket, TLS configuration, and paths for the target server.

## Filesystem permissions

The PHP process needs write access to `storage/` and `uploads/`. Do not make the whole repository writable by the web server. Protect `.env`, SQL files, logs, backups, and generated exports from direct web access.

## Database and backups

Use the versioned SQL migrations for existing MySQL installations. Back up the database and runtime data before applying migrations, and store backup archives outside the public document root with restricted permissions. See [`database/backups/README.md`](../database/backups/README.md).

## Post-deployment checks

1. Open the login page over HTTPS.
2. Sign in with a non-default administrator account.
3. Verify role/module permissions.
4. Create and edit a test inventory record.
5. Confirm installation stock deductions.
6. Generate a report preview and export.
7. Verify uploads, notifications, and password recovery.
8. Check server logs for PHP warnings and database errors.
