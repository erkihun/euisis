<?php

declare(strict_types=1);

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    foreach (['organizations.view', 'organizations.manage'] as $perm) {
        Permission::findOrCreate($perm, 'web');
    }

    Role::findOrCreate('Super Admin', 'web')->givePermissionTo(['organizations.view', 'organizations.manage']);
    Role::findOrCreate('Viewer', 'web')->givePermissionTo(['organizations.view']);
});

function makeOrgType(): OrganizationType
{
    return OrganizationType::query()->create(['code' => 'dept', 'name_en' => 'Department']);
}

function makeOrg(OrganizationType $type, string $code = 'ORG-1'): Organization
{
    return Organization::query()->create([
        'organization_type_id' => $type->id,
        'code' => $code,
        'name_en' => 'Test Organization',
        'status' => OrganizationStatus::Active,
    ]);
}

function managerUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('Super Admin');

    return $user;
}

function viewerUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('Viewer');

    return $user;
}

// ── Index ──────────────────────────────────────────────────────────────────

test('guests are redirected from the organizations index', function (): void {
    $this->get(route('organizations.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view the organizations index', function (): void {
    $this->actingAs(viewerUser())
        ->get(route('organizations.index'))
        ->assertOk();
});

// ── Create page ────────────────────────────────────────────────────────────

test('users without organizations.manage cannot access the create page', function (): void {
    $this->actingAs(viewerUser())
        ->get(route('organizations.create'))
        ->assertForbidden();
});

test('users with organizations.manage can access the create page', function (): void {
    $this->actingAs(managerUser())
        ->get(route('organizations.create'))
        ->assertOk();
});

// ── Store ──────────────────────────────────────────────────────────────────

test('users without organizations.manage cannot create an organization', function (): void {
    $type = makeOrgType();

    $this->actingAs(viewerUser())
        ->post(route('organizations.store'), [
            'organization_type_id' => $type->id,
            'code' => 'ORG-NEW',
            'name_en' => 'New Org',
            'status' => 'active',
        ])
        ->assertForbidden();
});

test('users with organizations.manage can create an organization', function (): void {
    $type = makeOrgType();

    $this->actingAs(managerUser())
        ->post(route('organizations.store'), [
            'organization_type_id' => $type->id,
            'code' => 'ORG-NEW',
            'name_en' => 'New Org',
            'status' => 'active',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('organizations', ['code' => 'ORG-NEW']);
});

// ── Edit page ──────────────────────────────────────────────────────────────

test('users without organizations.manage cannot access the edit page', function (): void {
    $org = makeOrg(makeOrgType());

    $this->actingAs(viewerUser())
        ->get(route('organizations.edit', $org))
        ->assertForbidden();
});

test('users with organizations.manage can access the edit page', function (): void {
    $org = makeOrg(makeOrgType());

    $this->actingAs(managerUser())
        ->get(route('organizations.edit', $org))
        ->assertOk();
});

// ── Update ─────────────────────────────────────────────────────────────────

test('users without organizations.manage cannot update an organization', function (): void {
    $org = makeOrg(makeOrgType());

    $this->actingAs(viewerUser())
        ->patch(route('organizations.update', $org), [
            'organization_type_id' => $org->organization_type_id,
            'code' => $org->code,
            'name_en' => 'Changed',
            'status' => 'active',
        ])
        ->assertForbidden();
});

test('users with organizations.manage can update an organization', function (): void {
    $org = makeOrg(makeOrgType());

    $this->actingAs(managerUser())
        ->patch(route('organizations.update', $org), [
            'organization_type_id' => $org->organization_type_id,
            'code' => $org->code,
            'name_en' => 'Updated Name',
            'status' => 'active',
        ])
        ->assertRedirect(route('organizations.show', $org));

    expect($org->fresh()->name_en)->toBe('Updated Name');
});

// ── Archive ────────────────────────────────────────────────────────────────

test('users without organizations.manage cannot archive an organization', function (): void {
    $org = makeOrg(makeOrgType());

    $this->actingAs(viewerUser())
        ->delete(route('organizations.archive', $org))
        ->assertForbidden();
});

test('archiving sets status to archived and redirects to index', function (): void {
    $org = makeOrg(makeOrgType());

    $this->actingAs(managerUser())
        ->delete(route('organizations.archive', $org))
        ->assertRedirect(route('organizations.index'));

    expect($org->fresh()->status)->toBe(OrganizationStatus::Archived);
});
