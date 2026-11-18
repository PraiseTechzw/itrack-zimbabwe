# Changelog

All notable changes to iTrack Zimbabwe are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and releases use [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added

- Persistent **Settings** page with General, Company, Inventory & low-stock alerts, Notifications/email, Currency & VAT, Security & access, and system information sections. Values are stored in a new `app_settings` table and applied across the application.
- Configurable **low-stock alerts**: a global default reorder level, per-item reorder levels on inventory forms, alert banners on Dashboard and Inventory, optional throttled email summaries, and a "send alert now" action.
- Low-stock watch panel on the Settings page showing items at or below their effective reorder level.
- `tests/settings.php` regression suite for settings persistence, threshold precedence and alert gating.

### Changed

- Reorder levels are now editable in single and bulk inventory screens; an item's own level overrides the configured default.
- Application name, tagline, registration toggle, password rules, session lifetime, public URL, currency, VAT default, mail sender and company/invoice details are now driven by saved settings.

## [1.1.0] – 2026-11-18

### Added

- Hard drive inventory category with an alphanumeric serial, selectable for camera installations and returned to stock on deinstallation.
- VAT rate on inventory items, with VAT-inclusive selling price shown in the register and included in the Excel export.
- Installed quantities column in the downloadable inventory workbook, derived from active installations.
- Database-backed demand aggregates (requisition requests and fulfilled installations) feeding AI business-insight forecasts for trackers, cameras and FLS.
- Returned equipment section on the inventory page and an installation-device/camera picker on the deinstallation form.
- Regression suite covering identifier rules, installation stock, deinstallation returns, demand aggregates and workbook generation (`php tests/run.php`).

### Changed

- All outgoing email (password resets and low-stock alerts) now sends through the Resend HTTP API instead of PHP's `mail()`. Configure it with `RESEND_API_KEY` and a verified `MAIL_FROM` address; the PHP `curl` extension is required.
- Dashboard inventory value card is now labelled **Cost total**.
- Camera-only installations no longer offer a device SIM line; only the camera SIM line applies.
- SIM line identifiers are limited to 10 digits; FLS/camera serials remain numeric and hard-drive serials are alphanumeric.
- Expanded contribution guidance with local setup, branch naming, MVC conventions, migration expectations, verification commands, security requirements, and MIT licensing notes.
- Reworked the pull-request template with code, UI, API, database, deployment, documentation, screenshot, and security checklists.
- Replaced invalid issue-form markup with valid Markdown issue templates for bug reports and feature requests.
- Added GitHub issue configuration for private security-vulnerability reporting.
- Expanded the code of conduct, architecture guide, security guide, and documentation index.
- Removed duplicate `.env` entries from `.gitignore`.

### Fixed

- Deinstallation no longer requires a chassis number.
- Deinstalling a tracker or camera now records which item was removed and restores its equipment to stock exactly once.

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
