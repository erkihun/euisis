# Cafeteria Module

The scan module enforces provider-specific data isolation. Provider operators are scoped through `cafeteria_provider_users`; users with all-provider permissions or the `Super Admin` role can access all cafeteria providers.

Scan processing uses a frontend-generated `scan_nonce` for idempotency. Replaying a fulfilled request returns the existing transaction result and does not create a duplicate transaction. The physical ID card QR remains reusable; raw QR token data is not stored in cafeteria transactions.

Subsidy usage modes are fixed:

- `single_day`
- `use_remaining_week`

Custom operator-entered subsidy amounts are not allowed.

Consumed subsidy days are persisted in `cafeteria_transaction_consumed_days`. Remaining-week scans mark eligible days from the scan date through Friday, excluding closed days, public holidays, special no-subsidy days, employee exclusions, and already consumed days.

Today’s Scans returns provider-scoped rows with employee name, employee number, organization, organization unit, position, provider, scan time, usage mode, subsidy amount, consumed day count, status, and extra/payable indicators.
## Provider Portal Separation

The cafeteria provider portal is separate from the admin cafeteria module. Provider-facing pages live under `/cafeteria/portal`, use `cafeteria.portal.*` route names, and render through `CafeteriaProviderPortalLayout`. Admin cafeteria routes remain under `/cafeteria` and continue to use the admin layout and RBAC.

Provider portal data is scoped to the selected active provider assignment. Provider-only users are redirected away from `/dashboard`; staff/admin users must have an active provider assignment plus portal permissions to enter the provider portal.

## Provider Transaction Exports

Provider portal transaction exports are available under `/cafeteria/portal/transactions/export*` and are always scoped to the selected active cafeteria provider assignment. Providers cannot pass an arbitrary provider ID to export another cafeteria's records.

Supported format:

- CSV transaction export
- CSV payment claim export
- XLSX transaction export
- XLSX payment claim export
- PDF transaction export
- PDF payment claim export

Export columns are limited to billing-safe transaction fields: transaction number, Gregorian/display dates, scan time, employee number/name, employee institution and organization unit, position, provider, provider institution, usage mode, consumed days, subsidy amount, employee payable amount, status, rejection reason, operator, and created at.

Exports do not include QR token data, QR hashes, national ID, phone number, private file paths, or raw metadata. Each export writes an audit log with provider, period, filters, row count, totals, actor, IP, and user agent metadata.
