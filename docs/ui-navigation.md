# UI Navigation

## Cafeteria Provider Portal

The provider portal navigation is isolated in `resources/js/Layouts/CafeteriaProviderPortalLayout.tsx`. It does not reuse the admin sidebar or top navigation.

Visible provider items:

- Provider Dashboard
- Scan ID
- Transactions
- Ledger
- Daily Menus
- Food Orders
- Reports
- Provider Profile

Admin-only modules such as organizations, employees, ID cards, roles, permissions, system settings, audit logs, and code rules are not shown in the provider portal.
