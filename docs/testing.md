# Testing Strategy

## Goals

- Prove scope-based authorization is enforced.
- Prove structure and assignment history are preserved.
- Prove card workflow and verification rules are enforced securely.
- Prove sensitive actions create audit logs.

## Test Layers

- Feature tests for API endpoints, policies, web updates, and workflow rules
- Basic web access tests for scoped pages
- Action/service behavior verified through feature-level lifecycle tests using in-memory SQLite

## Core Coverage

- organization subtree authorization
- hierarchy version publishing
- hierarchy version CRUD permissions, validation, and archive rules
- hierarchy tree view/edit permissions and scoped relation mutation routes
- hierarchy relation add/update/remove audit coverage
- mojibake regression checks for hierarchy Amharic localization files
- transitive closure depth generation for published hierarchy versions
- employee registration and initial assignment creation
- duplicate-flag detection foundation
- employee transfer lifecycle
- transfer page access and permission checks
- transfer approval preserving employee UUID and employee number
- position CRUD permission checks and unique `job_position_code` validation
- organization parent-option scope filtering, search behavior, and child-creation validation
- service type CRUD permission checks, validation, and audit logging
- entitlement rule CRUD permission checks, validation, and audit logging
- code rule CRUD permission checks, preview, archive/restore conflict handling, and audit logging
- generated organization codes, employee numbers, job position codes, and ID card numbers
- dashboard permission, scope, PII, and bounded-list behavior
- stable employee UUID across transfer
- employee update audit logging
- card approval/print/issue ordering rules
- card replacement linkage and prior-card suspension
- secure QR token handling
- verification denial rules
- provider/service scope enforcement
- Sanctum token ability enforcement for provider APIs
- audit log creation for sensitive actions
- profile settings updates, safe profile photo upload, shared avatar props, and national ID privacy
- user create/edit validation for profile photo, national ID, phone number, gender, roles, blank passwords, self-deactivation, and last Super Admin protection

## How To Run

```bash
php artisan test
```

Current automated suite runs against SQLite in memory through `phpunit.xml.dist`. PostgreSQL-specific constraints such as partial indexes still need separate integration coverage.

## Remaining Gaps

- full print-batch rendering and PDF validation
- settlement engine edge cases
- offline sync cryptographic signature tests
- browser-level acceptance coverage for all admin pages
- remaining database-to-UI coverage gaps for approval, API client, device binding, transport, cafeteria, consumer, and support modules
- high-contention concurrent generation stress coverage for `code_rules` still relies on transactional design review more than true parallel test execution
- PostgreSQL-only constraint tests for single current assignment / single current card rules
- deeper workflow branching tests for transfer rejection/cancellation permutations
- dashboard export and scheduled snapshot coverage once those features exist

## Recycle Bin

Recycle Bin coverage verifies protected access, soft-delete metadata, restore behavior, and record_deleted / record_restored audit events for supported configuration records.
# Cafeteria Scan Checks

- Provider operators must only receive assigned cafeteria providers in scan provider lists.
- Scan requests require `scan_nonce`; replayed fulfilled nonces must not create another transaction.
- `custom_amount` and requested subsidy amount inputs are rejected by backend validation.
- Remaining-week scans must persist consumed dates and the calendar response must mark those dates consumed.
- Today’s Scans must include employee identity fields and must not expose national ID, raw QR token, or QR hashes.
