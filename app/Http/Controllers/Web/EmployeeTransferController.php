<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Transfers\ApproveEmployeeTransferAction;
use App\Actions\Transfers\CancelEmployeeTransferAction;
use App\Actions\Transfers\CompleteEmployeeTransferAction;
use App\Actions\Transfers\ConfirmCurrentOrganizationTransferAction;
use App\Actions\Transfers\ConfirmReceivingOrganizationTransferAction;
use App\Actions\Transfers\RejectEmployeeTransferAction;
use App\Actions\Transfers\RequestEmployeeTransferAction;
use App\Actions\Transfers\SubmitEmployeeTransferAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveEmployeeTransferRequest;
use App\Http\Requests\CancelEmployeeTransferRequest;
use App\Http\Requests\ConfirmCurrentOrganizationTransferRequest;
use App\Http\Requests\ConfirmReceivingOrganizationTransferRequest;
use App\Http\Requests\RejectEmployeeTransferRequest;
use App\Http\Requests\StoreEmployeeTransferRequest;
use App\Http\Requests\SubmitEmployeeTransferRequest;
use App\Http\Requests\UpdateEmployeeTransferRequest;
use App\Http\Resources\EmployeeTransferResource;
use App\Models\Employee;
use App\Models\EmployeeTransfer;
use App\Models\Organization;
use App\Models\Position;
use App\Models\User;
use App\Services\OrganizationScope\OrganizationScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeTransferController extends Controller
{
    public function index(Request $request, OrganizationScopeService $organizationScopeService): Response
    {
        $this->authorize('viewAny', EmployeeTransfer::class);

        $accessibleOrganizationIds = $organizationScopeService->accessibleOrganizationIds($request->user());
        $status = $request->string('status')->toString();

        $transfers = EmployeeTransfer::query()
            ->with([
                'employee:id,employee_number,full_name,status',
                'fromOrganization:id,name_en',
                'toOrganization:id,name_en',
                'fromPosition:id,job_position_code,title_en',
                'toPosition:id,job_position_code,title_en',
                'requestedBy:id,name',
            ])
            ->when(
                ! $request->user()->hasRole(['Super Admin', 'City Admin']),
                fn ($query) => $query->where(function ($inner) use ($accessibleOrganizationIds): void {
                    $inner->whereIn('from_organization_id', $accessibleOrganizationIds)
                        ->orWhereIn('to_organization_id', $accessibleOrganizationIds);
                })
            )
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($request->string('from_organization_id')->toString() !== '', fn ($query) => $query->where('from_organization_id', $request->string('from_organization_id')->toString()))
            ->when($request->string('to_organization_id')->toString() !== '', fn ($query) => $query->where('to_organization_id', $request->string('to_organization_id')->toString()))
            ->when($request->string('requested_by')->toString() !== '', fn ($query) => $query->where('requested_by', $request->integer('requested_by')))
            ->when($request->string('date_from')->toString() !== '', fn ($query) => $query->whereDate('effective_date', '>=', $request->string('date_from')->toString()))
            ->when($request->string('date_to')->toString() !== '', fn ($query) => $query->whereDate('effective_date', '<=', $request->string('date_to')->toString()))
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->whereHas('employee', function ($employeeQuery) use ($search): void {
                    $employeeQuery->where('employee_number', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Transfers/Index', [
            'transfers' => EmployeeTransferResource::collection($transfers)->resolve(),
            'filters' => $request->only(['status', 'from_organization_id', 'to_organization_id', 'search', 'date_from', 'date_to', 'requested_by']),
            'organizations' => Organization::query()
                ->whereIn('id', $accessibleOrganizationIds)
                ->orderBy('name_en')
                ->get(['id', 'name_en']),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'can' => [
                'create' => $request->user()?->can('create', EmployeeTransfer::class) ?? false,
            ],
        ]);
    }

    public function pending(Request $request): Response
    {
        $request->merge(['status' => 'submitted']);

        return $this->index($request, app(OrganizationScopeService::class));
    }

    public function create(Request $request, OrganizationScopeService $organizationScopeService): Response
    {
        $this->authorize('create', EmployeeTransfer::class);

        $accessibleOrganizationIds = $organizationScopeService->accessibleOrganizationIds($request->user());
        $selectedEmployeeId = $request->string('employee')->toString();

        return Inertia::render('Transfers/Create', [
            'selectedEmployeeId' => $selectedEmployeeId !== '' ? $selectedEmployeeId : null,
            'employees' => Employee::query()
                ->with(['currentAssignment.organization', 'currentAssignment.position'])
                ->whereHas('currentAssignment', fn ($query) => $query->whereIn('organization_id', $accessibleOrganizationIds))
                ->orderBy('full_name')
                ->get()
                ->map(fn (Employee $employee) => [
                    'id' => $employee->id,
                    'employee_number' => $employee->employee_number,
                    'full_name' => $employee->full_name,
                    'status' => $employee->status?->value ?? $employee->status,
                    'current_assignment' => $employee->currentAssignment ? [
                        'organization_id' => $employee->currentAssignment->organization_id,
                        'organization_name' => $employee->currentAssignment->organization?->name_en,
                        'position_id' => $employee->currentAssignment->position_id,
                        'position_name' => $employee->currentAssignment->position?->title_en,
                    ] : null,
                ]),
            'organizations' => Organization::query()
                ->whereIn('id', $accessibleOrganizationIds)
                ->orderBy('name_en')
                ->get(['id', 'name_en']),
            'positions' => Position::query()
                ->where('is_active', true)
                ->where(function ($query) use ($accessibleOrganizationIds): void {
                    $query->whereNull('organization_id')
                        ->orWhereIn('organization_id', $accessibleOrganizationIds);
                })
                ->orderBy('job_position_code')
                ->get(['id', 'job_position_code', 'title_en', 'organization_id']),
        ]);
    }

    public function store(StoreEmployeeTransferRequest $request, RequestEmployeeTransferAction $action): RedirectResponse
    {
        $employee = Employee::query()->with('currentAssignment')->findOrFail($request->string('employee_id')->toString());
        $transfer = $action->execute(
            $employee,
            $request->string('to_organization_id')->toString(),
            $request->user(),
            $request->input('transfer_reason'),
            $request->date('effective_date')?->toDateString(),
            $request->string('to_position_id')->toString() ?: null,
        );

        return to_route('employee-transfers.show', $transfer)
            ->with('flash', ['message' => __('Transfer request created.'), 'type' => 'success']);
    }

    public function show(EmployeeTransfer $employeeTransfer): Response
    {
        $this->authorize('view', $employeeTransfer);

        $employeeTransfer->load([
            'employee.currentAssignment.organization',
            'employee.currentAssignment.position',
            'fromOrganization',
            'toOrganization',
            'fromPosition',
            'toPosition',
            'currentAssignment',
            'requestedBy',
            'currentOrganizationConfirmedBy',
            'receivingOrganizationConfirmedBy',
            'approvedBy',
            'rejectedBy',
        ]);

        return Inertia::render('Transfers/Show', [
            'transfer' => (new EmployeeTransferResource($employeeTransfer))->resolve(),
        ]);
    }

    public function edit(EmployeeTransfer $employeeTransfer, OrganizationScopeService $organizationScopeService): Response
    {
        $this->authorize('update', $employeeTransfer);

        $accessibleOrganizationIds = $organizationScopeService->accessibleOrganizationIds(request()->user());
        $employeeTransfer->load(['employee.currentAssignment.organization', 'employee.currentAssignment.position', 'toOrganization', 'toPosition']);

        return Inertia::render('Transfers/Edit', [
            'transfer' => (new EmployeeTransferResource($employeeTransfer))->resolve(),
            'organizations' => Organization::query()
                ->whereIn('id', $accessibleOrganizationIds)
                ->orderBy('name_en')
                ->get(['id', 'name_en']),
            'positions' => Position::query()
                ->where('is_active', true)
                ->where(function ($query) use ($accessibleOrganizationIds): void {
                    $query->whereNull('organization_id')
                        ->orWhereIn('organization_id', $accessibleOrganizationIds);
                })
                ->orderBy('job_position_code')
                ->get(['id', 'job_position_code', 'title_en', 'organization_id']),
        ]);
    }

    public function update(UpdateEmployeeTransferRequest $request, EmployeeTransfer $employeeTransfer): RedirectResponse
    {
        $employeeTransfer->update($request->validated());

        return to_route('employee-transfers.show', $employeeTransfer)
            ->with('flash', ['message' => __('Transfer request updated.'), 'type' => 'success']);
    }

    public function submit(SubmitEmployeeTransferRequest $request, EmployeeTransfer $employeeTransfer, SubmitEmployeeTransferAction $action): RedirectResponse
    {
        $action->execute($employeeTransfer, $request->user());

        return back()->with('flash', ['message' => __('Transfer submitted.'), 'type' => 'success']);
    }

    public function confirmCurrentOrganization(
        ConfirmCurrentOrganizationTransferRequest $request,
        EmployeeTransfer $employeeTransfer,
        ConfirmCurrentOrganizationTransferAction $action,
    ): RedirectResponse {
        $action->execute($employeeTransfer, $request->user());

        return back()->with('flash', ['message' => __('Current organization confirmed the transfer.'), 'type' => 'success']);
    }

    public function confirmReceivingOrganization(
        ConfirmReceivingOrganizationTransferRequest $request,
        EmployeeTransfer $employeeTransfer,
        ConfirmReceivingOrganizationTransferAction $action,
    ): RedirectResponse {
        $action->execute($employeeTransfer, $request->user());

        return back()->with('flash', ['message' => __('Receiving organization confirmed the transfer.'), 'type' => 'success']);
    }

    public function approve(
        ApproveEmployeeTransferRequest $request,
        EmployeeTransfer $employeeTransfer,
        ApproveEmployeeTransferAction $action,
    ): RedirectResponse {
        $action->execute($employeeTransfer, $request->user());

        return back()->with('flash', ['message' => __('Transfer approved and completed.'), 'type' => 'success']);
    }

    public function reject(
        RejectEmployeeTransferRequest $request,
        EmployeeTransfer $employeeTransfer,
        RejectEmployeeTransferAction $action,
    ): RedirectResponse {
        $action->execute($employeeTransfer, $request->user(), $request->string('rejection_reason')->toString());

        return back()->with('flash', ['message' => __('Transfer rejected.'), 'type' => 'success']);
    }

    public function cancel(
        CancelEmployeeTransferRequest $request,
        EmployeeTransfer $employeeTransfer,
        CancelEmployeeTransferAction $action,
    ): RedirectResponse {
        $action->execute($employeeTransfer, $request->user());

        return back()->with('flash', ['message' => __('Transfer cancelled.'), 'type' => 'success']);
    }

    public function complete(
        Request $request,
        EmployeeTransfer $employeeTransfer,
        CompleteEmployeeTransferAction $action,
    ): RedirectResponse {
        $this->authorize('complete', $employeeTransfer);

        $action->execute($employeeTransfer, $request->user());

        return back()->with('flash', ['message' => __('Transfer completed.'), 'type' => 'success']);
    }
}
