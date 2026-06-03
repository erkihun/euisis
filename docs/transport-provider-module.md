# Transport Provider Module

The transport module is implemented as a first-class provider service under the general provider platform.

## Architecture

- Transport provider identity lives in `providers`.
- Transport provider users live in `provider_users`.
- Transport service enablement lives in `provider_services` with `service_types.code = transport`.
- Transport-specific operational data lives in `transport_provider_profiles`, `transport_routes`, `transport_vehicles`, `transport_drivers`, `transport_trips`, `transport_passes`, and `transport_transactions`.
- Transport users are never stored in the admin `users` table.

## Provider Portal

Provider routes are under `/provider/portal/transport` and require:

- `auth:provider`
- `provider.portal`
- `provider.portal.context`
- `provider.service:transport`

The portal uses `resources/js/Layouts/TransportProviderLayout.tsx` and only renders transport navigation. Cafeteria navigation remains in the cafeteria portal layout.

## Scan Flow

Transport scans accept the stable ID-card QR reference and a `scan_nonce`.

The scan service:

- resolves the stable QR reference through `CardQrPayloadService`
- confirms the provider has transport service enabled
- verifies card, employee assignment, route access, and active transport pass
- rejects duplicate `scan_nonce`
- stores only `qr_reference_hash`, never the raw QR value
- scopes every persisted transaction to the authenticated provider user's `provider_id`

## Admin Registration

Admin transport provider registration creates:

- one `providers` row with provider type `TRANSPORT`
- one active `provider_services` row for `transport`
- one `transport_provider_profiles` row
- optionally one `provider_users` credential account

## Verification

Validated commands:

- `php artisan route:list --name=transport`
- `php artisan migrate`
- `php artisan test tests\Feature\CafeteriaProviderPortalSeparationTest.php`
- `vendor\bin\pint ...`
- `npm run build`
