<?php

declare(strict_types=1);

use App\Models\Occupation;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    foreach ([
        'occupations.viewAny',
        'occupations.view',
        'occupations.create',
        'occupations.update',
        'occupations.delete',
        'occupations.restore',
        'occupations.export',
    ] as $perm) {
        Permission::findOrCreate($perm, 'web');
    }

    Role::findOrCreate('Occ Admin', 'web')->syncPermissions([
        'occupations.viewAny', 'occupations.view', 'occupations.create',
        'occupations.update', 'occupations.delete', 'occupations.restore',
    ]);
    Role::findOrCreate('Occ Viewer', 'web')->syncPermissions(['occupations.viewAny']);
});

function occAdmin(): User
{
    $u = User::factory()->create();
    $u->assignRole('Occ Admin');

    return $u;
}

function occViewer(): User
{
    $u = User::factory()->create();
    $u->assignRole('Occ Viewer');

    return $u;
}

test('isco_code is required when creating an occupation', function (): void {
    $this->actingAs(occAdmin())
        ->post(route('occupations.store'), [
            'name_en' => 'Test',
            'is_active' => true,
        ])
        ->assertSessionHasErrors('isco_code');
});

test('isco_code must be unique', function (): void {
    Occupation::query()->create([
        'code' => '2512',
        'isco_code' => '2512',
        'name_en' => 'Existing',
        'is_active' => true,
    ]);

    $this->actingAs(occAdmin())
        ->post(route('occupations.store'), [
            'isco_code' => '2512',
            'name_en' => 'Duplicate',
            'is_active' => true,
        ])
        ->assertSessionHasErrors('isco_code');
});

test('admin can create occupation with ISCO code', function (): void {
    $this->actingAs(occAdmin())
        ->post(route('occupations.store'), [
            'isco_code' => '2611',
            'isco_major_group_code' => '2',
            'isco_unit_group_code' => '2611',
            'name_en' => 'Lawyers',
            'skill_level' => '4',
            'is_active' => true,
        ])
        ->assertRedirect();

    expect(Occupation::query()->where('isco_code', '2611')->exists())->toBeTrue();
});

test('invalid isco_code is rejected', function (): void {
    $this->actingAs(occAdmin())
        ->post(route('occupations.store'), [
            'isco_code' => 'BAD-CODE!',
            'name_en' => 'Test',
            'is_active' => true,
        ])
        ->assertSessionHasErrors('isco_code');
});

test('admin can update occupation', function (): void {
    $occ = Occupation::query()->create([
        'code' => '4110',
        'isco_code' => '4110',
        'name_en' => 'Clerks',
        'is_active' => true,
    ]);

    $this->actingAs(occAdmin())
        ->patch(route('occupations.update', $occ), [
            'isco_code' => '4110',
            'name_en' => 'General Office Clerks',
            'is_active' => true,
        ])
        ->assertRedirect();

    expect($occ->fresh()->name_en)->toBe('General Office Clerks');
});

test('unauthorized user cannot create occupation', function (): void {
    $this->actingAs(occViewer())
        ->post(route('occupations.store'), [
            'isco_code' => '2611',
            'name_en' => 'Test',
            'is_active' => true,
        ])
        ->assertForbidden();
});
