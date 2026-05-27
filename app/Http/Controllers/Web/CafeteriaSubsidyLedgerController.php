<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\CafeteriaSubsidyLedgerResource;
use App\Models\CafeteriaSubsidyLedger;
use App\Models\Employee;
use App\Services\Cafeteria\CafeteriaLedgerService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CafeteriaSubsidyLedgerController extends Controller
{
    public function __construct(private readonly CafeteriaLedgerService $ledgerService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CafeteriaSubsidyLedger::class);

        $employeeId = $request->string('employee_id')->toString() ?: null;

        $query = CafeteriaSubsidyLedger::query()
            ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
            ->when($request->string('date_from')->toString(), fn ($q, $v) => $q->whereDate('ledger_date', '>=', $v))
            ->when($request->string('date_to')->toString(), fn ($q, $v) => $q->whereDate('ledger_date', '<=', $v))
            ->orderByDesc('ledger_date')
            ->orderByDesc('created_at');

        $entries = $query->paginate(50)->withQueryString();

        $employee = $employeeId ? Employee::find($employeeId) : null;
        $balance  = $employee ? $this->ledgerService->getBalance($employee) : null;

        return Inertia::render('Cafeteria/Ledger/Index', [
            'entries'  => CafeteriaSubsidyLedgerResource::collection($entries)->resolve(),
            'meta'     => [
                'current_page' => $entries->currentPage(),
                'last_page'    => $entries->lastPage(),
                'total'        => $entries->total(),
                'per_page'     => $entries->perPage(),
            ],
            'filters'  => $request->only(['employee_id', 'date_from', 'date_to']),
            'employee' => $employee?->only(['id', 'full_name', 'employee_number']),
            'balance'  => $balance,
        ]);
    }
}
