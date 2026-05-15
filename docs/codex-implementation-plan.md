# Codex Implementation Plan

## Current Status

- Repository started as documentation-only.
- Requirements were extracted locally from `docs/addis_ababa_employee_id_service_platform_full_english.docx` into `docs/requirements-extracted.md`.
- MVP implementation target is a secure Laravel modular monolith foundation.
- The implemented local foundation runs on Laravel 12 because the available local PHP runtime is `8.2.12`; production target remains Laravel 13 / PHP 8.4 once the runtime is upgraded.

## Assumptions

- Laravel 13 remains the preferred production target because the repository is new and the procurement documents do not strictly require Laravel 12.
- Local implementation uses Laravel 12 for executable compatibility with the currently available PHP 8.2 runtime.
- PostgreSQL is the primary production database, but SQLite may be used for selected fast tests where schema features allow it.
- Redis, Horizon, Pulse, and private S3-compatible storage are part of the target architecture, even if local development begins with file/local fallbacks.
- The first implementation pass should favor secure vertical slices over exhaustive feature completeness.
- The system is internal-government software, so privacy, auditability, and historical correctness take precedence over convenience.
- Demo seed data must remain explicitly marked as demo and must not represent real people.

## Interpreted Requirements

- Organization hierarchy must be configurable, versioned, effective-dated, and queryable by subtree.
- Employee identity must be stable across transfers and restructuring.
- Assignment history must be effective-dated and retained.
- ID cards must have a controlled workflow from request through issuance, suspension, replacement, and verification.
- QR verification must not expose personal data and must rely on signed tokens or token pairs with hashed storage.
- Provider APIs must be scope-limited, rate-limited, and return minimal data.
- Every sensitive change and access-sensitive operation must create immutable audit records.
- Authorization must combine RBAC with organization-scoped access control.

## Module Boundaries

- Organizations
  - organization types, organizations, hierarchy versions, edges, name history, subtree resolution
- Security & Access
  - users, scopes, roles/permissions, provider API access, device bindings, security events
- Employees
  - employee registry, assignments, status history, documents, duplicate checks, transfers
- ID Cards
  - requests, approvals, token generation, print batches, issuance, replacements, verification
- Entitlements & Services
  - service types, providers, rules, entitlements, service authorization, transactions, settlements
- Workflow
  - approval workflows, steps, approvals, task assignments
- Audit & Reporting
  - immutable audit logs, export audit, dashboard/reporting foundation

## Database Model Summary

### Organization Master Data

- `organization_types`
- `organizations`
- `hierarchy_versions`
- `organization_edges`
- `organization_name_histories`
- `organization_closure_paths`
- `organization_change_requests`

### Security & Scope

- `users`
- Spatie permission tables
- `user_organization_scopes`
- `code_rules`
- `code_generation_logs`
- `api_clients`
- `device_bindings`
- `security_events`

### Employee Registry

- `positions`
- `employees`
- `employee_assignments`
- `employment_status_histories`
- `employee_documents`
- `employee_duplicate_flags`

### ID Card

- `card_requests`
- `card_templates`
- `id_cards`
- `card_print_batches`
- `card_print_batch_items`
- `card_issuances`
- `card_replacements`
- `card_verifications`

### Entitlements & Service Usage

- `service_types`
- `service_providers`
- `service_provider_users`
- `entitlement_rules`
- `entitlements`
- `service_transactions`
- `provider_settlements`
- `settlement_runs`

### Transport

- `transport_plans`
- `transport_routes`
- `transport_usages`
- `transport_settlements`

### Cafeteria / Coupon

- `meal_plans`
- `coupon_programs`
- `coupon_balances`
- `cafeteria_transactions`

### Consumer Association

- `consumer_memberships`
- `consumer_limits`
- `consumer_transactions`

### Workflow & Support

- `approval_workflows`
- `approval_steps`
- `approvals`
- `task_assignments`
- `notifications`
- `support_tickets`
- `audit_logs`

## Planned Migration List

1. Core users and cache/session/queue tables from Laravel defaults
2. Spatie permission tables
3. Organization master data tables
4. Security and scope tables
5. Employee registry tables
6. ID card lifecycle tables
7. Service type/provider/entitlement tables
8. Service transactions and settlement foundation tables
9. Workflow tables
10. Audit and support tables

## Authorization Model

- Authentication: Laravel auth + Sanctum
- RBAC: Spatie roles and permissions
- Organization scope: custom `OrganizationScopeService` plus `user_organization_scopes`
- Provider scope: service provider and service type checks in middleware/services
- Policies:
  - `OrganizationPolicy`
  - `EmployeePolicy`
  - `IdCardPolicy`
  - `CardRequestPolicy`
  - `EntitlementPolicy`
  - `ServiceTransactionPolicy`
  - `AuditLogPolicy`
  - `ReportPolicy`

## API List

- `POST /api/v1/cards/verify`
- `POST /api/v1/services/{serviceType}/authorize`
- `POST /api/v1/services/{serviceType}/transactions`
- `GET /api/v1/employees/{employee}/entitlements`
- `GET /api/v1/providers/{provider}/settlements/{period}`
- `POST /api/v1/offline-sync/transactions`

## UI Page List

- Dashboard overview
- Organization types list/form
- Organizations list/form/detail
- Hierarchy versions list/detail/tree
- Employees list/form/detail/history/documents
- Card requests list/detail/workflow
- Cards verification test page
- Print batch foundation pages
- Providers list/detail/transactions/settlements
- Entitlements list/grant-revoke foundation
- Audit logs viewer
- Reports foundation pages

## Test Plan

- Policy and organization-scope tests
- Hierarchy versioning and structure-history tests
- Employee registration, duplicate detection, and transfer tests
- Card workflow and verification tests
- Provider/service authorization tests
- Audit log creation tests
- API safe-response and rate-limit tests
- Basic web page access tests for scoped roles

## Implementation Phases

### Phase 1

- Completed
  - Scaffolded Laravel 12 + Inertia React TypeScript foundation compatible with the local PHP 8.2 runtime
  - Built core database foundation and enums
  - Implemented organization scope service, hierarchy publishing, closure-path generation, and hierarchy pages
  - Implemented employee registration, duplicate detection, scoped list/detail/update, and a dedicated employee transfer workflow module
  - Implemented position management with unique `job_position_code`, scoped CRUD, and archive/restore flow
  - Implemented card request, verify, approve, print, issue, incident, replacement, token generation, and verification foundation
  - Implemented service authorization/transaction API foundation with provider token ability checks and duplicate reference guard
  - Rebuilt the dashboard as a scoped executive overview with aggregate KPI cards, charts, workflow queues, alerts, recent activity, and provider-aware visibility rules
  - Added demo seeders using the real hierarchy publication path
  - Added operational Inertia pages for organizations, employees, transfers, positions, cards, providers, entitlements, dashboard, and audit
- Added full CRUD UI for service types and entitlement rules with EN/AM localization, permission-aware navigation, and audit logging
- Added reusable Code Rules CRUD with preview, archive/restore, generation logs, and transactional code generation services
- Added Recycle Bin for supported soft-deleted configuration/business records with restore permissions and audit logging
- Integrated code generation with organization, organization type, employee, position, and ID card creation flows
- Fixed the Organizations add-child flow so parent options are scope-aware, searchable, and no longer limited to root-only records
  - Added full Hierarchy Versions CRUD with dedicated permissions, draft-only edit/archive rules, publish validation, tree routes, and sidebar navigation
  - Upgraded Hierarchy Version tree pages with modal add/edit/remove relation workflows, scoped organization search, localized summaries, and relation-specific audit events
  - Added `docs/database-ui-coverage.md`, `docs/ui-module-map.md`, and `docs/authorization-matrix.md` to track schema-to-UI coverage
  - Added passing feature coverage for scope, hierarchy, employee lifecycle, transfer lifecycle, position CRUD, card lifecycle, verification, provider API safety, and audit

### Current Phase 1 Gaps

- provider/device binding is schema-only and not yet enforced per device
- offline sync remains verification-first and needs signed payload handling plus transaction persistence rules
- print batch management is still embedded in the card creation flow rather than surfaced as a dedicated batch module
- database-to-UI coverage is still incomplete for approval workflows, API clients, device bindings, transport, cafeteria, consumer association, notifications, and support tickets
- export/report actions need explicit permission-gated implementation and audit events
- bulk import and archival review flows for positions are not yet implemented
- transfer approval queues and multi-step workflow configuration remain code-driven rather than fully workflow-administered
- dashboard exports and scheduled executive snapshots are not yet implemented
- draft hierarchy publishing is still required before newly attached child organizations appear in the published organization tree
- the current hierarchy editor is modal-and-form driven rather than drag-and-drop
- Recycle Bin excludes historical workflow, audit, ID card lifecycle, employee assignment, and service transaction records by design

### Phase 2 Backlog

- PDF card batches and production print rendering
- Deeper settlement engine
- Offline sync hardening
- Consumer association/POS integration
- Notifications and real-time dashboards
- Reporting warehouse and advanced exports

## Risks and Unresolved Questions

- Final official organization master data, legal codes, and Amharic spellings are not yet validated.
- Card-number format, print template standards, and approval thresholds may require business confirmation.
- Provider API device-binding and signed-request details need operational policy decisions.
- Transfer lifecycle ownership between current organization HR, receiving organization HR, and city-level approvers may still need final business confirmation.
- Position catalog ownership must be confirmed: citywide master catalog, organization-local catalogs, or a hybrid model.
- PostgreSQL-specific features such as partial indexes and generated UUIDv7 defaults need environment confirmation during deployment.
- Some local tests may require PostgreSQL rather than SQLite because of stricter indexing and partial-constraint behavior.
