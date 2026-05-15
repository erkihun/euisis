<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\IdCards\ApproveCardRequestAction;
use App\Actions\IdCards\CancelCardRequestAction;
use App\Actions\IdCards\RejectCardRequestAction;
use App\Actions\IdCards\SubmitCardRequestAction;
use App\Actions\IdCards\VerifyCardRequestDataAction;
use App\Enums\CardRequestType;
use App\Http\Controllers\Controller;
use App\Http\Requests\IdCards\ApproveCardRequestRequest;
use App\Http\Requests\IdCards\CancelCardRequestRequest;
use App\Http\Requests\IdCards\RejectCardRequestRequest;
use App\Http\Requests\IdCards\StoreCardRequestRequest;
use App\Http\Requests\IdCards\VerifyCardRequestRequest;
use App\Models\CardRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CardRequestController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', CardRequest::class);

        $cardRequests = CardRequest::query()
            ->with(['employee.currentAssignment.organization', 'requester', 'reviewer', 'approver', 'rejecter'])
            ->orderByDesc('created_at')
            ->paginate(25);

        return Inertia::render('CardRequests/Index', [
            'cardRequests' => $cardRequests,
            'can' => [
                'create' => request()->user()?->can('create', CardRequest::class),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', CardRequest::class);

        $employees = Employee::query()
            ->with('currentAssignment.organization')
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'employee_number', 'full_name', 'status', 'current_assignment_id']);

        return Inertia::render('CardRequests/Create', [
            'employees' => $employees,
            'requestTypes' => array_column(CardRequestType::cases(), 'value'),
        ]);
    }

    public function store(StoreCardRequestRequest $request, SubmitCardRequestAction $submitCardRequestAction): RedirectResponse
    {
        $employee = Employee::query()->findOrFail($request->string('employee_id')->toString());
        $this->authorize('view', $employee);

        $requestType = $request->filled('request_type')
            ? CardRequestType::from($request->string('request_type')->toString())
            : CardRequestType::New;

        $submitCardRequestAction->execute(
            $employee,
            $request->user(),
            $request->input('reason'),
            $requestType,
        );

        return redirect()->route('card-requests.index')->with('success', 'Card request submitted.');
    }

    public function show(CardRequest $cardRequest): Response
    {
        $this->authorize('view', $cardRequest);

        $cardRequest->load([
            'employee.currentAssignment.organization',
            'employee.currentAssignment.position',
            'requester',
            'reviewer',
            'approver',
            'rejecter',
            'canceller',
            'previousCard',
            'cards',
        ]);

        $user = request()->user();

        return Inertia::render('CardRequests/Show', [
            'cardRequest' => $cardRequest,
            'can' => [
                'view' => $user?->can('view', $cardRequest),
                'verify' => $user?->can('verify', $cardRequest),
                'approve' => $user?->can('approve', $cardRequest),
                'reject' => $user?->can('reject', $cardRequest),
                'cancel' => $user?->can('cancel', $cardRequest),
            ],
        ]);
    }

    public function verify(VerifyCardRequestRequest $request, CardRequest $cardRequest, VerifyCardRequestDataAction $action): RedirectResponse
    {
        $action->execute($cardRequest, $request->user(), $request->input('notes'));

        return back()->with('success', 'Card request data verified.');
    }

    public function approve(ApproveCardRequestRequest $request, CardRequest $cardRequest, ApproveCardRequestAction $action): RedirectResponse
    {
        $action->execute($cardRequest, $request->user(), $request->input('notes'));

        return back()->with('success', 'Card request approved. Card is now pending print.');
    }

    public function reject(RejectCardRequestRequest $request, CardRequest $cardRequest, RejectCardRequestAction $action): RedirectResponse
    {
        $action->execute($cardRequest, $request->user(), $request->string('rejection_reason')->toString());

        return back()->with('success', 'Card request rejected.');
    }

    public function cancel(CancelCardRequestRequest $request, CardRequest $cardRequest, CancelCardRequestAction $action): RedirectResponse
    {
        $action->execute($cardRequest, $request->user(), $request->input('cancellation_reason'));

        return back()->with('success', 'Card request cancelled.');
    }
}
