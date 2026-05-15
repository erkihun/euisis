<?php

declare(strict_types=1);

namespace App\Actions\IdCards;

use App\Models\IdCard;

class GenerateCardTokenAction
{
    /**
     * Generate a secure random token for the card QR code.
     *
     * Stores only the SHA-256 hash in the database.
     * Returns the plaintext token ONCE — caller must encode it into the QR payload.
     * QR payload format: {"ref":"<card_uuid>","t":"<plaintext_token>"}
     * NEVER stored in plaintext. NEVER contains employee PII.
     */
    public function execute(IdCard $card): string
    {
        $rawToken = bin2hex(random_bytes(32)); // 64-char hex string

        $card->update([
            'token_hash' => hash('sha256', $rawToken),
            'token_version' => ($card->token_version ?? 0) + 1,
            'token_last_rotated_at' => now(),
        ]);

        // Return only the raw token (no PII, just entropy)
        return $rawToken;
    }
}
