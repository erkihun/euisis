<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportingLineResource;
use App\Models\InstitutionOffice;
use App\Models\InstitutionOfficeRelationship;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\OrganizationUnitRelationship;
use App\Services\OrganizationRelationships\ReportingLineService;
use Illuminate\Http\Request;

class ReportingLineController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->can('functional-reporting.viewReports') || $request->user()?->can('relationships.viewAny'), 403);

        $officeLines = InstitutionOfficeRelationship::query()->active()->secondary()->with('sourceOffice')->get();
        $unitLines = OrganizationUnitRelationship::query()->active()->secondary()->with('sourceUnit')->get();

        return ReportingLineResource::collection($officeLines->concat($unitLines));
    }

    public function organization(Request $request, Organization $organization, ReportingLineService $service)
    {
        abort_unless($request->user()?->can('functional-reporting.viewReports') || $request->user()?->can('relationships.viewAny'), 403);

        return ReportingLineResource::collection(
            $service->getOfficesReportingToOrganization($organization)
                ->concat($service->getUnitsReportingToOrganization($organization)),
        );
    }

    public function institutionOffice(Request $request, InstitutionOffice $institutionOffice, ReportingLineService $service)
    {
        abort_unless($request->user()?->can('functional-reporting.viewReports') || $request->user()?->can('relationships.viewAny'), 403);

        return ReportingLineResource::collection($service->getAllActiveReportingLines($institutionOffice));
    }

    public function organizationUnit(Request $request, OrganizationUnit $organizationUnit, ReportingLineService $service)
    {
        abort_unless($request->user()?->can('functional-reporting.viewReports') || $request->user()?->can('relationships.viewAny'), 403);

        return ReportingLineResource::collection($service->getAllActiveReportingLines($organizationUnit));
    }
}
