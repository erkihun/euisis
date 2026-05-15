<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Audit\WriteAuditLogAction;
use App\Actions\IdCards\ActivateCardAction;
use App\Actions\IdCards\IssueCardAction;
use App\Actions\IdCards\ReplaceCardAction;
use App\Actions\IdCards\ReportLostOrDamagedCardAction;
use App\Actions\IdCards\RevokeCardAction;
use App\Enums\AuditEventType;
use App\Enums\CardStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\IdCards\ActivateCardRequest;
use App\Http\Requests\IdCards\IssueCardRequest;
use App\Http\Requests\IdCards\ReplaceCardRequest;
use App\Http\Requests\IdCards\ReportDamagedCardRequest;
use App\Http\Requests\IdCards\ReportLostCardRequest;
use App\Http\Requests\IdCards\RevokeCardRequest;
use App\Models\IdCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IdCardController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', IdCard::class);

        $cards = IdCard::query()
            ->with(['employee.currentAssignment.organization', 'employee.currentAssignment.position', 'previousCard'])
            ->orderByDesc('created_at')
            ->paginate(25);

        return Inertia::render('IdCards/Index', [
            'cards' => $cards,
            'can' => [
                'create' => request()->user()?->can('create', IdCard::class),
                'submitRequest' => request()->user()?->can('id-cards.submitRequest') || request()->user()?->can('cards.manage'),
                'createPrintBatch' => request()->user()?->can('id-cards.createPrintBatch') || request()->user()?->can('cards.manage'),
            ],
        ]);
    }

    public function show(IdCard $card): Response
    {
        $this->authorize('view', $card);

        $card->load([
            'employee.currentAssignment.organization',
            'employee.currentAssignment.position',
            'cardRequest.requester',
            'cardRequest.reviewer',
            'cardRequest.approver',
            'previousCard',
            'replacementCard',
            'verifications',
            'issuance.issuer',
            'replacements.newCard',
        ]);

        $user = request()->user();

        return Inertia::render('IdCards/Show', [
            'card' => $card,
            'can' => [
                'view' => $user?->can('view', $card),
                'update' => $user?->can('update', $card),
                'print' => $card->status === CardStatus::PendingPrint && $user?->can('print', $card),
                'issue' => $card->status === CardStatus::Printed && $user?->can('issue', $card),
                'activate' => $card->status === CardStatus::Issued && $user?->can('activate', $card),
                'reportLost' => in_array($card->status, [CardStatus::Active, CardStatus::Issued], true) && $user?->can('reportLost', $card),
                'reportDamaged' => in_array($card->status, [CardStatus::Active, CardStatus::Issued], true) && $user?->can('reportDamaged', $card),
                'replace' => in_array($card->status, [CardStatus::Lost, CardStatus::Damaged, CardStatus::Expired, CardStatus::Active], true) && $user?->can('replace', $card),
                'revoke' => ! in_array($card->status, [CardStatus::Revoked, CardStatus::Replaced, CardStatus::Expired], true) && $user?->can('revoke', $card),
                'printAnytime' => $user?->can('printAnytime', $card),
                'exportPng' => $user?->can('exportPng', $card),
            ],
        ]);
    }

    public function preview(IdCard $card): Response
    {
        $this->authorize('view', $card);

        $card->load(['employee.currentAssignment.organization', 'employee.currentAssignment.position', 'cardRequest']);

        return Inertia::render('IdCards/Preview', [
            'card' => $card,
            'can' => [
                'print' => request()->user()?->can('print', $card),
            ],
        ]);
    }

    public function issue(IssueCardRequest $request, IdCard $card, IssueCardAction $issueCardAction): RedirectResponse
    {
        $issueCardAction->execute($card, $request->user(), $request->input('issued_to'), $request->input('received_by'));

        return back()->with('success', 'Card issued successfully.');
    }

    public function activate(ActivateCardRequest $request, IdCard $card, ActivateCardAction $activateCardAction): RedirectResponse
    {
        $activateCardAction->execute($card, $request->user(), $request->input('notes'));

        return back()->with('success', 'Card activated successfully.');
    }

    public function reportLost(ReportLostCardRequest $request, IdCard $card, ReportLostOrDamagedCardAction $action): RedirectResponse
    {
        $action->execute($card, 'lost', $request->user(), $request->input('reason'));

        return back()->with('success', 'Card reported as lost.');
    }

    public function reportDamaged(ReportDamagedCardRequest $request, IdCard $card, ReportLostOrDamagedCardAction $action): RedirectResponse
    {
        $action->execute($card, 'damaged', $request->user(), $request->input('reason'));

        return back()->with('success', 'Card reported as damaged.');
    }

    public function replace(ReplaceCardRequest $request, IdCard $card, ReplaceCardAction $replaceCardAction): RedirectResponse
    {
        $replaceCardAction->execute($card, $request->user(), $request->input('reason'));

        return redirect()->route('card-requests.index')->with('success', 'Replacement request submitted.');
    }

    public function revoke(RevokeCardRequest $request, IdCard $card, RevokeCardAction $revokeCardAction): RedirectResponse
    {
        $revokeCardAction->execute($card, $request->user(), $request->input('reason'));

        return back()->with('success', 'Card revoked.');
    }

    public function exportAudit(Request $request, IdCard $card, WriteAuditLogAction $writeAuditLogAction): JsonResponse
    {
        $side = $request->input('side', 'front');
        $action = $request->input('action', 'export_png');

        if ($action === 'print') {
            $this->authorize('printAnytime', $card);
            $eventType = AuditEventType::CardPrintedAnytime;
        } else {
            $this->authorize('exportPng', $card);
            $eventType = AuditEventType::CardExportedPng;
        }

        $writeAuditLogAction->execute(
            $eventType,
            $request->user(),
            $card,
            $card->employee?->currentAssignment?->organization_id,
            newValues: [
                'card_number' => $card->card_number,
                'side' => $side,
                'action' => $action,
            ],
        );

        return response()->json(['success' => true]);
    }
}
