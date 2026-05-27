<?php

declare(strict_types=1);

namespace App\Services\IdCards;

use App\Models\IdCard;

/**
 * Assembles an IdCardRenderData DTO from a fully-loaded IdCard model.
 *
 * Security contract:
 * - The QR payload is the stable public verification URL — no raw token/hash.
 * - Photo and logo paths are resolved to safe base64 data URIs.
 * - National ID and other PII fields are never included.
 * - QR URL never changes when services are added to the card.
 */
final readonly class IdCardRenderDataFactory
{
    public function __construct(
        private IdCardLayoutSettingsService $layoutService,
        private IdCardAssetResolver $assetResolver,
        private CardQrPayloadService $qrPayloadService,
    ) {}

    public function make(IdCard $card): IdCardRenderData
    {
        $employee = $card->employee;
        $assignment = $employee?->currentAssignment;
        $org = $assignment?->organization;
        $position = $assignment?->position;
        $layout = $this->layoutService->get();

        return new IdCardRenderData(
            cardId: $card->id,
            cardNumber: $card->card_number,
            status: $card->status->value,

            employeeNumber: $employee?->employee_number,
            fullNameEn: $employee?->full_name,
            fullNameAm: $employee?->metadata['name_am'] ?? null,

            organizationNameEn: $org?->name_en,
            organizationNameAm: $org?->name_am,
            positionTitleEn: $position?->title_en ?? null,
            positionTitleAm: $position?->title_am ?? null,

            issueDateFormatted: $card->issued_at?->format('d M Y'),
            expiryDateFormatted: $card->expires_at?->format('d M Y'),

            // Resolve files to base64 data URIs — never expose raw paths
            photoDataUri: $this->assetResolver->resolvePhotoPath($employee?->photo_path),
            logoDataUri: $this->assetResolver->resolveLogoPath($org?->logo_path),

            // Stable service-gateway QR URL — printed once, never changes on service updates.
            qrVerificationUrl: $this->qrPayloadService->buildStableQrUrl($card),

            layout: $layout,
        );
    }
}
