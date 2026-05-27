# Cafeteria Module

The scan module enforces provider-specific data isolation. Provider operators are scoped through `cafeteria_provider_users`; users with all-provider permissions or the `Super Admin` role can access all cafeteria providers.

Scan processing uses a frontend-generated `scan_nonce` for idempotency. Replaying a fulfilled request returns the existing transaction result and does not create a duplicate transaction. The physical ID card QR remains reusable; raw QR token data is not stored in cafeteria transactions.

Subsidy usage modes are fixed:

- `single_day`
- `use_remaining_week`

Custom operator-entered subsidy amounts are not allowed.

Consumed subsidy days are persisted in `cafeteria_transaction_consumed_days`. Remaining-week scans mark eligible days from the scan date through Friday, excluding closed days, public holidays, special no-subsidy days, employee exclusions, and already consumed days.

Today’s Scans returns provider-scoped rows with employee name, employee number, organization, organization unit, position, provider, scan time, usage mode, subsidy amount, consumed day count, status, and extra/payable indicators.
