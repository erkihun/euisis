<?php

declare(strict_types=1);

/**
 * Transfer tests have been moved to TransferModuleTest.php
 * This file retains position CRUD tests only.
 */

use App\Models\Position;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function (): void {
    foreach ([
        'positions.viewAny', 'positions.view', 'positions.create',
        'positions.update', 'positions.archive', 'positions.restore',
    ] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }
});

it('requires authentication for the positions index', function (): void {
    $this->get(route('positions.index'))
        ->assertRedirect(route('login'));
});

it('blocks positions index without permission', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('positions.index'))->assertForbidden();
});

it('allows positions index with permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('positions.viewAny');

    $this->actingAs($user)->get(route('positions.index'))->assertOk();
});
