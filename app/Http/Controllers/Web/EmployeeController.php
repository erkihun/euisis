<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Audit\WriteAuditLogAction;
use App\Actions\CodeRules\GenerateCodeAction;
use App\Actions\Employees\RegisterEmployeeAction;
use App\Actions\Transfers\RequestEmployeeTransferAction;
use App\Enums\AuditEventType;
use App\Enums\CodeRuleEntityType;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeTransferRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Http\Resources\EmployeeDetailResource;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\HierarchyVersion;
use App\Models\Organization;
use App\Models\Position;
use App\Services\OrganizationScope\OrganizationScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function index(Request $request, OrganizationScopeService $organizationScopeService): Response
    {
        $this->authorize('viewAny', Employee::class);

        $accessibleOrganizationIds = $organizationScopeService->accessibleOrganizationIds($request->user());

        $employees = Employee::query()
            ->with(['currentAssignment.organization', 'currentAssignment.position'])
            ->withCount('employeeDuplicateFlags')
            ->when(
                ! $request->user()->hasRole(['Super Admin', 'City Admin']),
                fn ($query) => $query->whereHas('currentAssignment', fn ($assignmentQuery) => $assignmentQuery->whereIn('organization_id', $accessibleOrganizationIds))
            )
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($nested) use ($search): void {
                    $nested->where('employee_number', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->string('status')->toString() !== '', fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->orderBy('full_name')
            ->get();

        return Inertia::render('Employees/Index', [
            'employees' => EmployeeResource::collection($employees)->resolve(),
            'filters' => $request->only(['search', 'status']),
            'can' => [
                'create' => $request->user()?->can('create', Employee::class) ?? false,
            ],
        ]);
    }

    public function create(OrganizationScopeService $organizationScopeService): Response
    {
        $this->authorize('create', Employee::class);

        $accessibleOrganizationIds = $organizationScopeService->accessibleOrganizationIds(request()->user());

        return Inertia::render('Employees/Create', [
            'organizations' => Organization::query()
                ->whereIn('id', $accessibleOrganizationIds)
                ->orderBy('name_en')
                ->get(['id', 'name_en']),
            'hierarchyVersions' => HierarchyVersion::query()->orderByDesc('effective_from')->get(['id', 'version_name', 'status']),
            'positions' => Position::query()->where('is_active', true)->orderBy('title_en')->get(['id', 'title_en', 'organization_id']),
        ]);
    }

    public function edit(Employee $employee, OrganizationScopeService $organizationScopeService): Response
    {
        $this->authorize('update', $employee);

        $employee->load(['currentAssignment.organization', 'currentAssignment.position']);

        return Inertia::render('Employees/Edit', [
            'employee' => (new EmployeeDetailResource($employee))->resolve(),
            'positions' => Position::query()->where('is_active', true)->orderBy('title_en')->get(['id', 'title_en']),
        ]);
    }

    public function show(Employee $employee, OrganizationScopeService $organizationScopeService): Response
    {
        $this->authorize('view', $employee);

        $employee->load([
            'currentAssignment.organization',
            'currentAssignment.position',
            'assignments.organization',
            'assignments.position',
            'documents',
            'employeeDuplicateFlags.matchedEmployee',
            'transfers.fromOrganization',
            'transfers.toOrganization',
        ]);

        return Inertia::render('Employees/Show', [
            'employee' => (new EmployeeDetailResource($employee))->resolve(),
            'organizations' => Organization::query()
                ->whereIn('id', $organizationScopeService->accessibleOrganizationIds(request()->user()))
                ->orderBy('name_en')
                ->get(['id', 'name_en']),
        ]);
    }

    public function store(
        EmployeeStoreRequest $request,
        RegisterEmployeeAction $registerEmployeeAction,
        GenerateCodeAction $generateCodeAction,
    ): RedirectResponse {
        $this->authorize('create', Employee::class);

        $positionId = $request->string('position_id')->toString() !== ''
            ? $request->string('position_id')->toString()
            : null;

        if ($positionId === null && $request->string('position_title')->toString() !== '') {
            $position = Position::query()->where([
                'organization_id' => $request->string('organization_id')->toString(),
                'title_en' => $request->string('position_title')->toString(),
            ])->first();

            if ($position === null) {
                $position = Position::query()->create([
                    'organization_id' => $request->string('organization_id')->toString(),
                    'title_en' => $request->string('position_title')->toString(),
                    'job_position_code' => $generateCodeAction->execute(
                        CodeRuleEntityType::Position,
                        ['organization_id' => $request->string('organization_id')->toString()],
                        $request->user(),
                        null,
                        'job_position_code',
                    ),
                    'is_active' => true,
                    'effective_from' => now()->toDateString(),
                ]);
            }

            $positionId = $position->id;
        }

        $employeeAttributes = $request->safe()->only([
            'employee_number',
            'first_name',
            'middle_name',
            'last_name',
            'phone',
            'email',
            'date_of_birth',
            'gender',
            'status',
            'national_id',
        ]);

        $employeeAttributes['full_name'] = trim(
            implode(' ', array_filter([
                $request->string('first_name')->toString(),
                $request->string('middle_name')->toString(),
                $request->string('last_name')->toString(),
            ]))
        );

        $employee = $registerEmployeeAction->execute(
            $employeeAttributes,
            [
                'organization_id' => $request->string('organization_id')->toString(),
                'hierarchy_version_id' => $request->string('hierarchy_version_id')->toString() ?: null,
                'position_id' => $positionId,
                'effective_from' => $request->date('effective_from')?->toDateString() ?? now()->toDateString(),
                'reason' => $request->input('reason'),
            ],
            $request->user(),
        );

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store(
                'employees/photos/'.$employee->id,
                'public'
            );
            $employee->update(['photo_path' => $path]);
        }

        return to_route('employees.show', $employee)
            ->with('flash', ['message' => __('Employee registered successfully.'), 'type' => 'success']);
    }

    public function update(
        EmployeeUpdateRequest $request,
        Employee $employee,
        WriteAuditLogAction $writeAuditLogAction,
    ): RedirectResponse {
        $this->authorize('update', $employee);

        $oldValues = $employee->toArray();

        $attributes = $request->safe()->except(['photo', 'remove_photo']);
        $attributes['full_name'] = trim(implode(' ', array_filter([
            $request->string('first_name')->toString(),
            $request->string('middle_name')->toString(),
            $request->string('last_name')->toString(),
        ])));

        if ($request->boolean('remove_photo') && $employee->photo_path) {
            Storage::disk('public')->delete($employee->photo_path);
            $attributes['photo_path'] = null;
        }

        if ($request->hasFile('photo')) {
            if ($employee->photo_path) {
                Storage::disk('public')->delete($employee->photo_path);
            }
            $attributes['photo_path'] = $request->file('photo')->store(
                'employees/photos/'.$employee->id,
                'public'
            );
        }

        $employee->update($attributes);

        $writeAuditLogAction->execute(
            AuditEventType::EmployeeUpdated,
            $request->user(),
            $employee->fresh(),
            $employee->currentAssignment?->organization_id,
            oldValues: $oldValues,
            newValues: $employee->fresh()->toArray(),
            request: $request,
        );

        return back()->with('flash', ['message' => __('Employee updated successfully.'), 'type' => 'success']);
    }

    public function transfer(
        EmployeeTransferRequest $request,
        Employee $employee,
        RequestEmployeeTransferAction $requestEmployeeTransferAction,
    ): RedirectResponse {
        $this->authorize('transfer', $employee);

        $transfer = $requestEmployeeTransferAction->execute(
            $employee,
            $request->string('organization_id')->toString(),
            $request->user(),
            $request->input('reason'),
            now()->toDateString(),
        );

        return to_route('employee-transfers.show', $transfer)
            ->with('flash', ['message' => __('Transfer draft created. Continue the workflow in Employee Transfers.'), 'type' => 'success']);
    }
}
