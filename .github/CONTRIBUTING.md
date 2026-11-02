# Contributing to iTrack Zimbabwe

Thank you for helping improve iTrack Zimbabwe. Contributions should be focused, tested, documented, and respectful of the project’s security and data-handling requirements.

## Before you start

Read the [installation guide](../docs/INSTALLATION.md), [feature guide](../docs/FEATURES.md), [development guide](../docs/DEVELOPMENT.md), and [security guide](../docs/SECURITY.md). For a substantial feature or data-model change, open an issue first so the proposed behavior and migration path can be discussed before implementation.

Do not include real customer data, production credentials, API keys, password-reset tokens, database dumps, private uploads, or personal information in commits, issues, pull requests, or screenshots.

## Development workflow

1. Fork the repository and create a focused branch from `main`, such as `feature/report-export`, `fix/login-redirect`, or `docs/deployment-guide`.
2. Configure a local `.env` and use SQLite for isolated development unless the change specifically targets MySQL behavior.
3. Keep changes within the existing MVC structure: controllers handle requests, models handle persistence, views render output, and helpers/services contain reusable behavior.
4. Add or update the route and permission checks when introducing a new authenticated action.
5. Add migrations for existing MySQL installations when the schema changes. Update seed data or SQLite initialization when local setup also needs the new structure.
6. Update the relevant documentation and current screenshot index when behavior, setup, routes, or UI changes.
7. Keep commits focused and use clear imperative messages, for example `fix: validate tracker identifiers` or `docs: clarify MySQL deployment`.

## Verification checklist

Run the PHP syntax check before opening a pull request:

```bash
find app api public includes tests -type f -name '*.php' -print0 | xargs -0 -n1 php -l
```

When the database is configured, run the lightweight helper check:

```bash
php tests/notification_service_test.php
```

Also perform the manual checks relevant to the change. For UI or workflow changes, verify login, role-aware navigation, the changed screen at desktop and mobile widths, and the affected create/edit/delete flow. For database changes, test a fresh SQLite initialization and an upgrade path using the relevant MySQL migration. For API changes, verify success, validation, authentication, and error responses.

The GitHub Actions workflow runs PHP syntax validation on pushes to `main` and on pull requests.

## Code and security expectations

Use prepared PDO statements and the existing model/database helpers. Validate and sanitize server-side, escape view output, preserve CSRF protection on state-changing requests, and apply the appropriate authentication and permission middleware. Do not introduce SQL built from unchecked input or commit secrets in any form.

Keep changes compatible with PHP 8.1 or newer unless the project explicitly raises its minimum version. Avoid unrelated formatting or generated-file changes in feature pull requests.

## Pull requests

Use the pull-request template. Include a concise summary, the reason for the change, testing performed, migration or deployment notes, and screenshots for visible UI changes. Call out known limitations or follow-up work explicitly. Maintainers may request documentation, tests, a migration, or a smaller change scope before review.

By contributing, you agree that your contribution may be distributed under the project’s [MIT License](../LICENSE).
