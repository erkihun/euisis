<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Cafeteria\GenerateDailyReportAction;
use App\Actions\Cafeteria\GenerateMonthlyReportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateCafeteriaReportRequest;
use App\Http\Resources\CafeteriaReportRunResource;
use App\Models\CafeteriaReportRun;
use App\Services\Cafeteria\CafeteriaProviderAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class CafeteriaReportController extends Controller
{
    public function __construct(private readonly CafeteriaProviderAccessService $providerAccess) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CafeteriaReportRun::class);

        $reports = CafeteriaReportRun::query()
            ->when($request->string('type')->toString(), fn ($q, $v) => $q->where('report_type', $v))
            ->when($request->string('organization_id')->toString(), fn ($q, $v) => $q->where('organization_id', $v))
            ->when($this->providerAccess->accessibleProviderIds($request->user()) !== [], function ($query) use ($request): void {
                $providerIds = $this->providerAccess->accessibleProviderIds($request->user());
                $query->where(function ($nested) use ($providerIds): void {
                    foreach ($providerIds as $providerId) {
                        $nested->orWhereJsonContains('filters->provider_ids', $providerId)
                            ->orWhereJsonContains('filters->provider_id', $providerId);
                    }
                });
            })
            ->orderByDesc('generated_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Cafeteria/Reports/Index', [
            'reports' => CafeteriaReportRunResource::collection($reports)->resolve(),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'total' => $reports->total(),
            ],
            'filters' => $request->only(['type', 'organization_id']),
            'can' => [
                'generate' => $request->user()?->can('generate', CafeteriaReportRun::class) ?? false,
            ],
        ]);
    }

    public function show(CafeteriaReportRun $cafeteriaReport): Response
    {
        $this->authorize('view', $cafeteriaReport);

        return Inertia::render('Cafeteria/Reports/Show', [
            'report' => (new CafeteriaReportRunResource($cafeteriaReport))->resolve(),
        ]);
    }

    public function generate(GenerateCafeteriaReportRequest $request, GenerateDailyReportAction $daily, GenerateMonthlyReportAction $monthly): RedirectResponse
    {
        $from = Carbon::parse($request->validated('period_start'));
        $to = Carbon::parse($request->validated('period_end'));
        $type = $request->validated('report_type');
        $orgId = $request->validated('organization_id');

        $report = match ($type) {
            'daily' => $daily->execute($from, $request->user(), $orgId, $request),
            'monthly' => $monthly->execute($from->year, $from->month, $request->user(), $orgId, $request),
            default => $monthly->execute($from->year, $from->month, $request->user(), $orgId, $request),
        };

        return to_route('cafeteria.reports.show', $report)
            ->with('flash', ['message' => __('cafeteria.reportGenerated'), 'type' => 'success']);
    }
}
