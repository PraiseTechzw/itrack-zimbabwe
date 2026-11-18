# Feature Guide

This guide maps the current iTrack Zimbabwe application to the workflows available in the interface.

## Access and identity

- Login and logout through the public authentication pages.
- Optional staff registration controlled by `APP_ALLOW_REGISTRATION`; keep it disabled in production unless required.
- Forgot-password requests and expiring reset tokens.
- Authenticated password changes.
- Session-based access control with CSRF protection.
- Role-aware module navigation and permission checks.
- Administrator permission matrix for enabling modules by role.

## Dashboard

The dashboard provides an operational overview with user count, tracked products, client accounts, GPS/installation records, low-stock alerts, a **Cost total** (stock valued at cost), quick actions, and a role-specific module launcher. When items reach their reorder level, a low-stock banner lists the affected items with links to Inventory and Settings.

## Inventory

Inventory supports the stock register for trackers, FLS units, cameras, hard drives, SIM cards/mobile lines, and related equipment. Records support category, item type, quantity, unit, cost price, selling price, VAT rate, reorder level, and serial/IMEI/SIM identifiers. SIM line numbers are limited to 10 digits; hard drives use an alphanumeric serial.

The inventory workflows include:

- Single-item create, edit, and delete.
- Bulk stock entry for several rows at once, entered manually.
- Download of the current inventory register as an Excel workbook, including VAT and the quantity currently installed per item.
- Serialized-item validation for FLS units, cameras, hard drives and SIM lines.
- Per-item reorder levels (editable in single and bulk screens) with a configurable global default from Settings.
- Low-stock alert banner listing items at or below their effective reorder level.
- A returned-equipment panel listing items restored to stock by deinstallations.
- Stock movement and quantity tracking.
- API listing through the inventory endpoint.

## Installations / GPS devices

The installations module records tracker deployments and operational device details. It supports tracker IMEI/serial values, SIM numbers, package types, status, and optional FLS/camera/hard-drive equipment selected from stock. Camera installations can associate a serialised hard drive, and camera-only packages use only the camera SIM line (no device SIM line).

The installation workflows include:

- Single installation create, edit, and delete.
- Bulk installation entry.
- Stock-backed equipment selection and quantity reduction.
- Installation and GPS device listing.
- Deinstallation by picking a specific installed tracker or camera; the chassis number is optional and all linked equipment is returned to stock.

## Settings and alerts

The Settings page is available to Administrators and Directors and stores values in `app_settings`, overriding environment defaults:

- **General** – application name, tagline, public URL, registration toggle and debug logging.
- **Company** – company name, address, phone, email, TIN, VAT number and bank details used on tax invoices.
- **Inventory & low-stock alerts** – master alert switch, default reorder level, and where banners appear (Dashboard, Inventory).
- **Notifications & email** – sender address, recipient list, email alert toggle, throttle interval and last-sent time.
- **Currency & VAT** – currency code/symbol and the default VAT rate for new inventory items.
- **Security & access** – minimum password length, session lifetime and failed-login limit.
- **System information** – environment, database driver, PHP version and shortcuts to permissions, reports and documentation.

An item is low when its stock is at or below its own reorder level, or the global default when no item-specific level is set. Optional email summaries are throttled by the configured interval; administrators can send one immediately from the Settings page.

## Parties and people

- **Clients** stores customer organizations and contact details.
- **Suppliers** stores procurement partners and contact details.
- **Users** manages staff accounts, departments, roles, and account status.
- **Notifications** supports system alerts, CRUD operations, and read-state handling.

## Procurement, sales, and requisitions

- **Purchases** captures purchase orders, suppliers, totals, remarks, and status updates.
- **Sales** captures customer transactions, sale types, package types, totals, and status visibility.
- **Requisitions** captures requestor contact and department, location, request and required dates, described items and quantities, and supports approval plus optional dispatcher/receiver sign-off.
- Relevant status changes create notifications for affected users or teams.

## Accounting

The accounting area contains separate workflows for:

- Accounting summary.
- Customer invoices with print-ready invoice views.
- Payments.
- Expenses.
- Petty cash.
- Cash book.

Accounting access is permission-controlled for finance roles and administrators.

## Reports and exports

Reports support operational and financial report records, preset periods, custom calendar ranges, previews, generation, and exports. The report services provide PDF and XLSX output.

The optional Reports assistant can answer questions about bounded aggregates such as recent sales, low stock, purchase-order totals, and database-backed demand for trackers, cameras and FLS. It compares requested quantities from installation requisitions with fulfilled installations to forecast the most common demand, using only aggregated figures. It does not execute generated SQL or receive customer names.

## Data and runtime behavior

- PDO is used for database access.
- SQLite is initialized automatically for local development.
- MySQL uses `database/schema.sql`, `database/seed.sql`, and versioned migration scripts.
- Runtime data, exports, sessions, logs, and uploads must be kept outside public access and protected with appropriate permissions.
