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

The dashboard provides an operational overview with user count, tracked products, client accounts, GPS/installation records, low-stock alerts, inventory value, quick actions, and a role-specific module launcher.

## Inventory

Inventory supports the stock register for trackers, FLS units, cameras, SIM cards/mobile lines, and related equipment. Records support category, item type, quantity, unit, cost price, selling price, reorder level, and optional numeric serial/IMEI/SIM identifiers.

The inventory workflows include:

- Single-item create, edit, and delete.
- Bulk stock entry for several rows at once.
- Serialized-item validation for FLS units and cameras.
- Stock movement and quantity tracking.
- API listing through the inventory endpoint.

## Installations / GPS devices

The installations module records tracker deployments and operational device details. It supports tracker IMEI/serial values, SIM numbers, package types, status, and optional FLS/camera/package equipment selected from stock.

The installation workflows include:

- Single installation create, edit, and delete.
- Bulk installation entry.
- Stock-backed equipment selection and quantity reduction.
- Installation and GPS device listing.

## Parties and people

- **Clients** stores customer organizations and contact details.
- **Suppliers** stores procurement partners and contact details.
- **Users** manages staff accounts, departments, roles, and account status.
- **Notifications** supports system alerts, CRUD operations, and read-state handling.

## Procurement, sales, and requisitions

- **Purchases** captures purchase orders, suppliers, totals, remarks, and status updates.
- **Sales** captures customer transactions, sale types, package types, totals, and status visibility.
- **Requisitions** captures internal requests and supports approval/decision workflows.
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

The optional Reports assistant can answer questions about bounded aggregates such as recent sales, low stock, and purchase-order totals when `OPENROUTER_API_KEY` is configured. It does not execute generated SQL or receive customer names.

## Data and runtime behavior

- PDO is used for database access.
- SQLite is initialized automatically for local development.
- MySQL uses `database/schema.sql`, `database/seed.sql`, and versioned migration scripts.
- Runtime data, exports, sessions, logs, and uploads must be kept outside public access and protected with appropriate permissions.
