# Changelog

All notable changes to iTrack Zimbabwe are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and releases use [Semantic Versioning](https://semver.org/).

## [1.0.1] – 2026-11-02

### Added

- Added the complete [MIT License](LICENSE) text with the `PraiseTechzw` copyright notice.
- Declared `MIT` explicitly in `composer.json` for package and tooling metadata.
- Updated the README license badge and license section.

## [1.0.0] – 2026-11-02

This is the first formal release of iTrack Zimbabwe: a PHP 8.1+ ERP-style operations platform for inventory, tracker installations, procurement, sales, accounting, requisitions, reporting, notifications, and user administration.

### Added

- Session-based login and logout with optional staff registration, password recovery, reset tokens, and authenticated password changes.
- CSRF protection, server-side validation, input sanitisation, permission middleware, role-aware navigation, and configurable role/module permissions.
- Dashboard with operational summaries, inventory value, low-stock alerts, user/client/device counts, quick actions, and role-specific module access.
- Inventory management for trackers, FLS units, cameras, SIM cards/mobile lines, and related equipment.
- Inventory quantities, reorder levels, cost and selling prices, units, serial numbers, IMEIs, and SIM identifiers.
- Bulk inventory entry with serialized-item validation and stock movement tracking.
- Tracker installation/GPS device records with tracker identifiers, SIM numbers, package types, statuses, and stock-backed equipment selection.
- Bulk installation workflows with inventory quantity deductions.
- Client, supplier, user, notification, settings, and permission administration.
- Purchase-order capture, supplier workflows, status changes, and team notifications.
- Sales capture, transaction visibility, package and sale types, and status handling.
- Internal requisitions with submission and approval/decision workflows.
- Accounting summary, invoices, print-ready invoice views, payments, expenses, petty cash, and cash book workflows.
- Reports with operational and financial report types, preset periods, custom calendar ranges, previews, generation, and exports.
- PDF and XLSX report services.
- Optional OpenRouter business insights using bounded report aggregates without AI-generated SQL execution or customer-name transmission.
- JSON endpoints for authentication, inventory, purchases, sales, requisitions, reports, accounting, and GPS/installations.
- PDO database support with automatic local SQLite initialization and MySQL deployment support.
- Versioned database migrations for device identifiers, SIM numbers, password-reset tokens, stock-backed installations/invoices, and mobile-line/SIM-card handling.
- Installation, feature, API, architecture, development, deployment, and security documentation.
- Complete current screenshot gallery covering public authentication, dashboard, mobile dashboard, all major modules, accounting subviews, forms, permissions, and settings.
- Animated product-tour GIF and README badges for a clearer project presentation.

### Changed

- Refreshed the root README with current setup, configuration, API, deployment, security, feature, and visual documentation.
- Replaced stale screenshot assets with clean screenshots captured from the running application.
- Expanded the documentation index with dedicated feature, API, and screenshot guides.
- Updated installation, development, and deployment guidance for the current environment-variable, SQLite, MySQL, PHP-FPM, and writable-directory behavior.
- Added the missing authentication actions to the route configuration for registration and password reset.
- Improved installation and inventory identifier handling for serialized trackers, FLS units, cameras, and SIM/mobile-line records.

### Fixed

- Corrected documentation links and removed obsolete screenshot references and review notes.
- Corrected README screenshot paths and verified that all referenced visual assets exist.
- Corrected the route configuration so authentication actions are represented consistently with the controller implementation.

### Verification

- PHP syntax validation passed across 136 PHP files.
- Public login, API responses, authenticated module pages, accounting subviews, form pages, and responsive dashboard captures were smoke-tested against the running application.
- README and screenshot-index links were checked for missing local targets.
- The release was committed and pushed to the `main` branch.

[1.0.1]: https://github.com/PraiseTechzw/itrack-zimbabwe/releases/tag/v1.0.1
[1.0.0]: https://github.com/PraiseTechzw/itrack-zimbabwe/releases/tag/v1.0.0
