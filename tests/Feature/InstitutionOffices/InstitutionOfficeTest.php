<?php

declare(strict_types=1);

use App\Enums\InstitutionOfficeLevel;
use App\Enums\InstitutionOfficeStatus;
use App\Enums\OrganizationStatus;
use App\Models\InstitutionOffice;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// ── Helpers ──────────────────────────────────────────────────────────────────

function makeInstitution(): Organization
{
    $type = OrganizationType::query()->firstOrCreate(
        ['code' => 'test-inst-type'],
        ['name_en' => 'Test Institution Type', 'is_demo' => false],
    );

    return Organization::query()->create([
        'organization_type_id' => $type->id,
        'code'                 => 'INST-' . uniqid(),
        'name_en'              => 'Test Institution',
        'status'               => OrganizationStatus::Active,
        'effective_from'       => now()->toDateString(),
    ]);
}

function ioAdminUser(): User
{
    foreach ([
        'institution-offices.viewAny',
        'institution-offices.view',
        'institution-offices.create',
        'institution-offices.update',
        'institution-offices.delete',
        'institution-offices.restore',
    ] as $perm) {
        Permission::findOrCreate($perm, 'web');
    }

    $role = Role::findOrCreate('IOAdmin', 'web');
    $role->syncPermissions([
        'institution-offices.viewAny',
        'institution-offices.view',
        'institution-offices.create',
        'institution-offices.update',
        'institution-offices.delete',
        'institution-offices.restore',
    ]);

    $user = User::factory()->create();
    $user->assignRole('IOAdmin');

    return $user;
}

function ioViewerUser(): User
{
    Permission::findOrCreate('institution-offices.viewAny', 'web');
    $role = Role::findOrCreate('IOViewer', 'web');
    $role->syncPermissions(['institution-offices.viewAny']);

    $user = User::factory()->create();
    $user->assignRole('IOViewer');

    return $user;
}

// ── 1. Index returns 200 for authorised user ─────────────────────────────────

test('authorised_user_can_view_institution_offices_index', function (): void {
    $user = ioAdminUser();
    $this->actingAs($user)->get(route('institution-offices.index'))
        ->assertOk();
});

// ── 2. Guest is redirected to login ──────────────────────────────────────────

test('guest_is_redirected_from_institution_offices_index', function (): void {
    $this->get(route('institution-offices.index'))
        ->assertRedirect(route('login'));
});

// ── 3. User without permission gets 403 ──────────────────────────────────────

test('user_without_permission_cannot_view_index', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('institution-offices.index'))
        ->assertForbidden();
});

// ── 4. Can create institution office ─────────────────────────────────────────

test('admin_can_create_institution_office', function (): void {
    $user = ioAdminUser();
    $institution = makeInstitution();

    $response = $this->actingAs($user)->post(route('institution-offices.store'), [
        'institution_id' => $institution->id,
        'office_level'   => InstitutionOfficeLevel::City->value,
        'office_code'    => 'OFF-' . uniqid(),
        'name_en'        => 'City Head Office',
        'status'         => InstitutionOfficeStatus::Active->value,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('institution_offices', [
        'institution_id' => $institution->id,
        'name_en'        => 'City Head Office',
        'office_level'   => InstitutionOfficeLevel::City->value,
    ]);
});

// ── 5. Validation fails if institution_id missing ─────────────────────────────

test('store_fails_validation_without_institution_id', function (): void {
    $user = ioAdminUser();

    $this->actingAs($user)->post(route('institution-offices.store'), [
        'office_level' => InstitutionOfficeLevel::City->value,
        'office_code'  => 'OFF-X',
        'name_en'      => 'Test',
    ])->assertSessionHasErrors(['institution_id']);
});

// ── 6. Validation fails if office_code missing ────────────────────────────────

test('store_fails_validation_without_office_code', function (): void {
    $user    = ioAdminUser();
    $institution = makeInstitution();

    $this->actingAs($user)->post(route('institution-offices.store'), [
        'institution_id' => $institution->id,
        'office_level'   => InstitutionOfficeLevel::City->value,
        'name_en'        => 'Test',
    ])->assertSessionHasErrors(['office_code']);
});

// ── 7. Office code must be unique ─────────────────────────────────────────────

test('office_code_must_be_unique', function (): void {
    $user    = ioAdminUser();
    $institution = makeInstitution();
    $code    = 'UNIQUE-' . uniqid();

    InstitutionOffice::query()->create([
        'institution_id' => $institution->id,
        'office_level'   => InstitutionOfficeLevel::City->value,
        'office_code'    => $code,
        'name_en'        => 'First',
        'status'         => InstitutionOfficeStatus::Active->value,
    ]);

    $this->actingAs($user)->post(route('institution-offices.store'), [
        'institution_id' => $institution->id,
        'office_level'   => InstitutionOfficeLevel::Branch->value,
        'office_code'    => $code,
        'name_en'        => 'Second',
    ])->assertSessionHasErrors(['office_code']);
});

// ── 8. Parent must belong to same institution ─────────────────────────────────

test('parent_office_must_belong_to_same_institution', function (): void {
    $user    = ioAdminUser();
    $inst1   = makeInstitution();
    $inst2   = makeInstitution();

    $parent = InstitutionOffice::query()->create([
        'institution_id' => $inst1->id,
        'office_level'   => InstitutionOfficeLevel::City->value,
        'office_code'    => 'PARENT-' . uniqid(),
        'name_en'        => 'Parent Office',
        'status'         => InstitutionOfficeStatus::Active->value,
    ]);

    $this->actingAs($user)->post(route('institution-offices.store'), [
        'institution_id'   => $inst2->id,
        'parent_office_id' => $parent->id,
        'office_level'     => InstitutionOfficeLevel::Branch->value,
        'office_code'      => 'CHILD-' . uniqid(),
        'name_en'          => 'Child',
    ])->assertSessionHasErrors(['parent_office_id']);
});

// ── 9. Can show office ────────────────────────────────────────────────────────

test('admin_can_view_institution_office', function (): void {
    $user    = ioAdminUser();
    $institution = makeInstitution();

    $office = InstitutionOffice::query()->create([
        'institution_id' => $institution->id,
        'office_level'   => InstitutionOfficeLevel::City->value,
        'office_code'    => 'SHOW-' . uniqid(),
        'name_en'        => 'Show Office',
        'status'         => InstitutionOfficeStatus::Active->value,
    ]);

    $this->actingAs($user)->get(route('institution-offices.show', $office))
        ->assertOk();
});

// ── 10. Can update office ─────────────────────────────────────────────────────

test('admin_can_update_institution_office', function (): void {
    $user    = ioAdminUser();
    $institution = makeInstitution();

    $office = InstitutionOffice::query()->create([
        'institution_id' => $institution->id,
        'office_level'   => InstitutionOfficeLevel::City->value,
        'office_code'    => 'UPD-' . uniqid(),
        'name_en'        => 'Original Name',
        'status'         => InstitutionOfficeStatus::Active->value,
    ]);

    $this->actingAs($user)->patch(route('institution-offices.update', $office), [
        'name_en' => 'Updated Name',
    ])->assertRedirect(route('institution-offices.show', $office));

    $this->assertDatabaseHas('institution_offices', [
        'id'      => $office->id,
        'name_en' => 'Updated Name',
    ]);
});

// ── 11. Can soft-delete office ────────────────────────────────────────────────

test('admin_can_delete_institution_office', function (): void {
    $user    = ioAdminUser();
    $institution = makeInstitution();

    $office = InstitutionOffice::query()->create([
        'institution_id' => $institution->id,
        'office_level'   => InstitutionOfficeLevel::Branch->value,
        'office_code'    => 'DEL-' . uniqid(),
        'name_en'        => 'To Delete',
        'status'         => InstitutionOfficeStatus::Active->value,
    ]);

    $this->actingAs($user)->delete(route('institution-offices.destroy', $office))
        ->assertRedirect(route('institution-offices.index'));

    $this->assertSoftDeleted('institution_offices', ['id' => $office->id]);
});

// ── 12. Can restore soft-deleted office ───────────────────────────────────────

test('admin_can_restore_institution_office', function (): void {
    $user    = ioAdminUser();
    $institution = makeInstitution();

    $office = InstitutionOffice::query()->create([
        'institution_id' => $institution->id,
        'office_level'   => InstitutionOfficeLevel::Branch->value,
        'office_code'    => 'RST-' . uniqid(),
        'name_en'        => 'To Restore',
        'status'         => InstitutionOfficeStatus::Active->value,
    ]);
    $office->delete();

    $this->actingAs($user)->post(route('institution-offices.restore', $office->id))
        ->assertRedirect(route('institution-offices.show', $office->id));

    $this->assertDatabaseHas('institution_offices', [
        'id'         => $office->id,
        'deleted_at' => null,
    ]);
});

// ── 13. Can move office to new parent ─────────────────────────────────────────

test('admin_can_move_institution_office', function (): void {
    $user    = ioAdminUser();
    $institution = makeInstitution();

    $parentA = InstitutionOffice::query()->create([
        'institution_id' => $institution->id,
        'office_level'   => InstitutionOfficeLevel::City->value,
        'office_code'    => 'PA-' . uniqid(),
        'name_en'        => 'Parent A',
        'status'         => InstitutionOfficeStatus::Active->value,
    ]);

    $parentB = InstitutionOffice::query()->create([
        'institution_id' => $institution->id,
        'office_level'   => InstitutionOfficeLevel::City->value,
        'office_code'    => 'PB-' . uniqid(),
        'name_en'        => 'Parent B',
        'status'         => InstitutionOfficeStatus::Active->value,
    ]);

    $child = InstitutionOffice::query()->create([
        'institution_id' => $institution->id,
        'parent_office_id' => $parentA->id,
        'office_level'   => InstitutionOfficeLevel::Branch->value,
        'office_code'    => 'CH-' . uniqid(),
        'name_en'        => 'Child',
        'status'         => InstitutionOfficeStatus::Active->value,
    ]);

    $this->actingAs($user)->post(route('institution-offices.move', $child), [
        'parent_office_id' => $parentB->id,
    ])->assertRedirect(route('institution-offices.show', $child));

    $this->assertDatabaseHas('institution_offices', [
        'id'               => $child->id,
        'parent_office_id' => $parentB->id,
    ]);
});

// ── 14. Move is blocked for circular hierarchy ────────────────────────────────

test('move_blocked_for_circular_hierarchy', function (): void {
    $user    = ioAdminUser();
    $institution = makeInstitution();

    $parent = InstitutionOffice::query()->create([
        'institution_id' => $institution->id,
        'office_level'   => InstitutionOfficeLevel::City->value,
        'office_code'    => 'CYC-P-' . uniqid(),
        'name_en'        => 'Parent',
        'status'         => InstitutionOfficeStatus::Active->value,
    ]);

    $child = InstitutionOffice::query()->create([
        'institution_id'   => $institution->id,
        'parent_office_id' => $parent->id,
        'office_level'     => InstitutionOfficeLevel::Branch->value,
        'office_code'      => 'CYC-C-' . uniqid(),
        'name_en'          => 'Child',
        'status'           => InstitutionOfficeStatus::Active->value,
    ]);

    // Trying to make parent a child of its own child — circular
    $this->actingAs($user)->post(route('institution-offices.move', $parent), [
        'parent_office_id' => $child->id,
    ])->assertSessionHasErrors(['parent_office_id']);
});

// ── 15. Viewer cannot create ──────────────────────────────────────────────────

test('viewer_cannot_create_institution_office', function (): void {
    $user    = ioViewerUser();
    $institution = makeInstitution();

    $this->actingAs($user)->post(route('institution-offices.store'), [
        'institution_id' => $institution->id,
        'office_level'   => InstitutionOfficeLevel::City->value,
        'office_code'    => 'VIEW-' . uniqid(),
        'name_en'        => 'Test',
    ])->assertForbidden();
});

// ── 16. Tree endpoint returns JSON ────────────────────────────────────────────

test('tree_endpoint_returns_json', function (): void {
    $user    = ioAdminUser();
    $institution = makeInstitution();

    InstitutionOffice::query()->create([
        'institution_id' => $institution->id,
        'office_level'   => InstitutionOfficeLevel::City->value,
        'office_code'    => 'TREE-' . uniqid(),
        'name_en'        => 'Root Office',
        'status'         => InstitutionOfficeStatus::Active->value,
    ]);

    $this->actingAs($user)
        ->getJson(route('institutions.offices.tree', $institution))
        ->assertOk()
        ->assertJsonStructure([['id', 'office_code', 'name_en', 'office_level', 'children']]);
});

// ── 17. Model casts work correctly ────────────────────────────────────────────

test('model_casts_office_level_enum', function (): void {
    $institution = makeInstitution();

    $office = InstitutionOffice::query()->create([
        'institution_id' => $institution->id,
        'office_level'   => InstitutionOfficeLevel::SubCity->value,
        'office_code'    => 'CAST-' . uniqid(),
        'name_en'        => 'Cast Test',
        'status'         => InstitutionOfficeStatus::Active->value,
    ]);

    expect($office->fresh()->office_level)->toBeInstanceOf(InstitutionOfficeLevel::class);
    expect($office->fresh()->office_level)->toBe(InstitutionOfficeLevel::SubCity);
});

// ── 18. Head office cannot have parent ───────────────────────────────────────

test('head_office_can_be_created_without_parent', function (): void {
    $user    = ioAdminUser();
    $institution = makeInstitution();

    $this->actingAs($user)->post(route('institution-offices.store'), [
        'institution_id' => $institution->id,
        'office_level'   => InstitutionOfficeLevel::City->value,
        'office_code'    => 'HEAD-' . uniqid(),
        'name_en'        => 'Head Office',
        'is_head_office' => true,
        'status'         => InstitutionOfficeStatus::Active->value,
    ])->assertRedirect();

    $this->assertDatabaseHas('institution_offices', [
        'institution_id' => $institution->id,
        'is_head_office' => true,
    ]);
});

// ── 19. Invalid office_level is rejected ──────────────────────────────────────

test('invalid_office_level_is_rejected', function (): void {
    $user    = ioAdminUser();
    $institution = makeInstitution();

    $this->actingAs($user)->post(route('institution-offices.store'), [
        'institution_id' => $institution->id,
        'office_level'   => 'invalid_level',
        'office_code'    => 'INV-' . uniqid(),
        'name_en'        => 'Bad Level',
    ])->assertSessionHasErrors(['office_level']);
});

// ── 20. SoftDelete: deleted offices don't appear in default query ─────────────

test('soft_deleted_office_does_not_appear_in_default_query', function (): void {
    $institution = makeInstitution();

    $office = InstitutionOffice::query()->create([
        'institution_id' => $institution->id,
        'office_level'   => InstitutionOfficeLevel::Woreda->value,
        'office_code'    => 'SOFT-' . uniqid(),
        'name_en'        => 'Soft Deleted',
        'status'         => InstitutionOfficeStatus::Active->value,
    ]);
    $office->delete();

    $found = InstitutionOffice::query()->where('id', $office->id)->first();
    expect($found)->toBeNull();

    $foundWithTrashed = InstitutionOffice::withTrashed()->where('id', $office->id)->first();
    expect($foundWithTrashed)->not->toBeNull();
});
