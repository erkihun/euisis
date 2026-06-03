<?php

declare(strict_types=1);

namespace App\Http\Controllers\Transfers;

use App\Actions\Transfers\UpdateTransferSettingsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transfers\UpdateTransferSettingsRequest;
use App\Models\TransferSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TransferSettingController extends Controller
{
    public function show(): Response
    {
        $this->authorize('view', TransferSetting::class);

        return Inertia::render('Transfers/Settings', [
            'settings' => TransferSetting::current(),
        ]);
    }

    public function update(
        UpdateTransferSettingsRequest $request,
        UpdateTransferSettingsAction $action,
    ): RedirectResponse {
        $this->authorize('update', TransferSetting::class);

        $action->execute($request->validated(), Auth::user());

        return to_route('transfer-settings.show')
            ->with('flash', ['message' => __('transfers.settingsUpdated'), 'type' => 'success']);
    }
}
