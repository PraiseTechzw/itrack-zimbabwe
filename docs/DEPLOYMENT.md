# Deployment Guide

## Production checklist

- Point the web server document root to [public](../public).
- Ensure PHP is enabled and the `pdo_mysql` extension is available if you plan to use MySQL.
- Make sure the application can write to [storage](../storage) and [uploads](../uploads).
- Review the base URL in [app/config/app.php](../app/config/app.php) and update it to match the deployment domain.
- Enable HTTPS and restrict access to sensitive files.

## Web server example

### Apache

Configure the site root to the public folder and allow PHP execution.

### Nginx

Use a location block that routes requests to the public directory and passes them to PHP-FPM.

## Security notes

- Keep PHP and web server software updated.
- Restrict write access to runtime directories.
- Avoid exposing database credentials in public repositories.
- Rotate default passwords after deployment.
