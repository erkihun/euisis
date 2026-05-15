<?php

declare(strict_types=1);

use App\Actions\Transfers\ApproveEmployeeTransferAction;
use App\Actions\Transfers\ConfirmCurrentOrganizationTransferAction;
use App\Actions\Transfers\RequestEmployeeTransferAction;
use App\Actions\Transfers\SubmitEmployeeTransferAction;
use App\Enums\AssignmentStatus;
use App\Enums\EmployeeStatus;
use App\Enums\OrganizationScopeType;
use App\Enums\OrganizationStatus;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\EmployeeAssignment;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Position;
use App\Models\User;
use App\Models\UserOrganizationScope;
use Illuminate\Support\Facades\Lang;
use Spatie\Permission\Models\Permission;

beforeEach(function (): void {
    foreach ([
        'transfers.viewAny', 'transfers.view', 'transfers.create', 'transfers.update', 'transfers.submit', 'transfers.confirmCurrentOrganization', 'transfers.confirmReceivingOrganization', 'transfers.approve', 'transfers.reject', 'transfers.cancel', 'transfers.complete',
        'positions.viewAny', 'positions.view', 'positions.create', 'positions.update', 'positions.archive', 'positions.restore',
    ] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }
});

function seedTransferTestOrganizations(): array
{
    $type = OrganizationType::query()->create(['code' => 'dept', 'name_en' => 'Department']);

    $from = Organization::query()->create([
        'organization_type_id' => $type->id,
        'code' => 'ORG-FROM',
        'name_en' => 'From Organization',
        'status' => OrganizationStatus::Active,
    ]);

    $to = Organization::query()->create([
        'organization_type_id' => $type->id,
        'code' => 'ORG-TO',
        'name_en' => 'To Organization',
        'status' => OrganizationStatus::Active,
    ]);

    return compact('type', 'from', 'to');
}

it('requires authentication for the transfer index', function (): void {
    $this->get(route('employee-transfers.index'))
        ->assertRedirect(route('login'));
});

it('blocks transfer index without permission and allows it with permission', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('employee-transfers.index'))
        ->assertForbidden();

    $user->givePermissionTo('transfers.viewAny');

    $this->actingAs($user)
        ->get(route('employee-transfers.index'))
        ->assertOk();
});

it('creates and approves a transfer while preserving employee identity', function (): void {
    extract(seedTransferTestOrganizations());

    $actor = User::factory()->create();
    $actor->givePermissionTo('transfers.create', 'transfers.submit', 'transfers.confirmCurrentOrganization', 'transfers.approve', 'transfers.viewAny', 'transfers.view');

    UserOrganizationScope::query()->create([
        'user_id' => $actor->id,
        'organization_id' => $from->id,
        'scope_type' => OrganizationScopeType::Self,
    ]);
    UserOrganizationScope::query()->create([
        'user_id' => $actor->id,
        'organization_id' => $to->id,
        'scope_type' => OrganizationScopeType::Self,
    ]);

    $position = Position::query()->create([
        'organization_id' => $from->id,
        'job_position_code' => 'POS-001',
        'title_en' => 'Officer',
        'is_active' => true,
        'effective_from' => now()->toDateString(),
    ]);

    $employee = Employee::query()->create([
        'employee_number' => 'EMP-900',
        'first_name' => 'Transfer',
        'last_name' => 'Employee',
        'full_name' => 'Transfer Employee',
        'status' => EmployeeStatus::Active,
    ]);

    $assignment = EmployeeAssignment::query()->create([
        'employee_id' => $employee->id,
        'organization_id' => $from->id,
        'position_id' => $position->id,
        'assignment_status' => AssignmentStatus::Active,
        'effective_from' => now()->toDateString(),
        'is_current' => true,
    ]);
    $employee->update(['current_assignment_id' => $assignment->id]);

    $transfer = app(RequestEmployeeTransferAction::class)->execute(
        $employee->fresh('currentAssignment', 'transfers'),
        $to->id,
        $actor,
        'Operational transfer',
        now()->addDay()->toDateString(),
        null,
    );

    app(SubmitEmployeeTransferAction::class)->execute($transfer, $actor);
    app(ConfirmCurrentOrganizationTransferAction::class)->execute($transfer->fresh(), $actor);
    $completed = app(ApproveEmployeeTransferAction::class)->execute($transfer->fresh(), $actor);

    expect($employee->fresh()->id)->toBe($employee->id)
        ->and($employee->fresh()->employee_number)->toBe('EMP-900')
        ->and($employee->fresh()->currentAssignment->organization_id)->toBe($to->id)
        ->and($completed->status->value)->toBe('completed')
        ->and(EmployeeAssignment::query()->where('employee_id', $employee->id)->where('assignment_status', 'closed')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('event_type', 'transfer_completed')->exists())->toBeTrue();
});

it('does not allow transfer creation for inactive employees', function (): void {
    extract(seedTransferTestOrganizations());

    $actor = User::factory()->create();
    $actor->givePermissionTo('transfers.create');

    $employee = Employee::query()->create([
        'employee_number' => 'EMP-901',
        'first_name' => 'Inactive',
        'last_name' => 'Employee',
        'full_name' => 'Inactive Employee',
        'status' => EmployeeStatus::Suspended,
    ]);

    $assignment = EmployeeAssignment::query()->create([
        'employee_id' => $employee->id,
        'organization_id' => $from->id,
        'assignment_status' => AssignmentStatus::Active,
        'effective_from' => now()->toDateString(),
        'is_current' => true,
    ]);
    $employee->update(['current_assignment_id' => $assignment->id]);

    expect(fn () => app(RequestEmployeeTransferAction::class)->execute($employee->fresh('currentAssignment', 'transfers'), $to->id, $actor))
        ->toThrow(DomainException::class);
});

it('supports position crud constraints and audit logging', function (): void {
    extract(seedTransferTestOrganizations());

    $user = User::factory()->create();
    $user->givePermissionTo('positions.viewAny', 'positions.view', 'positions.create', 'positions.update', 'positions.archive', 'positions.restore');

    UserOrganizationScope::query()->create([
        'user_id' => $user->id,
        'organization_id' => $from->id,
        'scope_type' => OrganizationScopeType::Self,
    ]);

    $this->actingAs($user)
        ->post(route('positions.store'), [
            'job_position_code' => 'POS-100',
            'title_en' => 'Director',
            'organization_id' => $from->id,
            'is_active' => true,
            'effective_from' => now()->toDateString(),
        ])
        ->assertRedirect();

    $position = Position::query()->where('job_position_code', 'POS-100')->firstOrFail();

    $this->actingAs($user)
        ->post(route('positions.store'), [
            'job_position_code' => 'POS-100',
            'title_en' => 'Duplicate',
            'organization_id' => $from->id,
            'is_active' => true,
            'effective_from' => now()->toDateString(),
        ])
        ->assertSessionHasErrors('job_position_code');

    $this->actingAs($user)
        ->patch(route('positions.update', $position), [
            'job_position_code' => 'POS-100',
            'title_en' => 'Senior Director',
            'title_am' => '',
            'organization_id' => $from->id,
            'description_en' => '',
            'description_am' => '',
            'grade_level' => 'G1',
            'job_family' => 'Leadership',
            'is_active' => true,
            'effective_from' => now()->toDateString(),
            'effective_to' => '',
        ])
        ->assertRedirect();

    $this->actingAs($user)
        ->delete(route('positions.archive', $position))
        ->assertRedirect();

    expect(Position::query()->withTrashed()->find($position->id)->deleted_at)->not->toBeNull()
        ->and(AuditLog::query()->where('event_type', 'position_updated')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('event_type', 'record_deleted')->exists())->toBeTrue();
});

it('has transfer and position localization files in english and amharic', function (): void {
    expect(Lang::get('transfers.title', [], 'en'))->toBe('Employee Transfers')
        ->and(Lang::get('positions.title', [], 'en'))->toBe('Job Positions')
        ->and(Lang::get('transfers.title', [], 'am'))->toBe('የሰራተኛ ዝውውሮች')
        ->and(Lang::get('positions.title', [], 'am'))->toBe('የስራ መደቦች');
});
