# Authorization Matrix

Last updated: 2026-05-12

This matrix summarizes the route-level permission groups currently exposed in the UI.

| Module | Route Prefix | Primary Permissions |
|---|---|---|
| Dashboard | `/dashboard` | `dashboard.view` |
| Organizations | `/organizations` | `organizations.view`, `organizations.manage`, `organizations.viewAny` |
| Organization Types | `/organization-types` | `organization-types.viewAny`, `organization-types.view`, `organization-types.create`, `organization-types.update`, `organization-types.archive`, `organization-types.restore` |
| Employees | `/employees` | `employees.view`, `employees.viewAny`, `employees.manage` |
| Employee Transfers | `/employee-transfers` | `transfers.viewAny`, `transfers.view`, `transfers.create`, `transfers.update`, `transfers.submit`, `transfers.confirmCurrentOrganization`, `transfers.confirmReceivingOrganization`, `transfers.approve`, `transfers.reject`, `transfers.cancel`, `transfers.complete` |
| Positions | `/positions` | `positions.viewAny`, `positions.view`, `positions.create`, `positions.update`, `positions.archive`, `positions.restore` |
| ID Cards | `/id-cards`, `/card-requests`, `/print-batches` | `id-cards.*`, `card-verifications.viewAny` |
| Service Types | `/service-types` | `service-types.viewAny`, `service-types.view`, `service-types.create`, `service-types.update`, `service-types.archive`, `service-types.restore` |
| Service Providers | `/service-providers` | `providers.viewAny` |
| Entitlement Rules | `/entitlement-rules` | `entitlement-rules.viewAny`, `entitlement-rules.view`, `entitlement-rules.create`, `entitlement-rules.update`, `entitlement-rules.archive`, `entitlement-rules.restore` |
| Code Rules | `/code-rules` | `code-rules.viewAny`, `code-rules.view`, `code-rules.create`, `code-rules.update`, `code-rules.archive`, `code-rules.restore`, `code-rules.preview`, `code-rules.generate`, `code-rules.manageOverrides` |
| Recycle Bin | `/recycle-bin` | `recycle-bin.view`, `recycle-bin.restore`, `recycle-bin.viewDetails`, module `*.viewDeleted`, module `*.restore` |
| Entitlements | `/entitlements` | `entitlements.view`, `entitlements.viewAny`, `entitlements.manage` |
| Audit Logs | `/audit-logs` | `audit.view`, `audit-logs.viewAny` |
| Users | `/users` | `users.viewAny`, `users.view`, `users.create`, `users.update`, `users.archive`, `users.restore`, `users.assignRoles` |
| Roles | `/roles` | `roles.viewAny`, `roles.view`, `roles.create`, `roles.update`, `roles.delete`, `roles.assignPermissions` |
| Permissions | `/permissions` | `permissions.viewAny`, `permissions.view` |
| System Settings | `/system-settings` | `system-settings.view`, `system-settings.update`, `system-settings.manage*` |

## Notes

- Backend policies remain the source of truth. The sidebar and action buttons only hide actions the current user cannot perform.
- Organization scope is enforced separately from RBAC for employee, transfer, and organization-sensitive routes.
- Provider API scope is enforced in API middleware and is not represented as a standalone web admin module yet.
