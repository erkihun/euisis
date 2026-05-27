<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('id_cards', function (Blueprint $table): void {
            // Stable public reference printed on the physical card.
            // Generated once; never regenerated when services are added.
            // Changes only when the card is replaced/reissued.
            $table->uuid('public_card_uuid')->nullable()->unique()->after('id');

            $table->string('qr_status', 16)->default('active')->after('qr_payload');
            $table->timestamp('qr_issued_at')->nullable()->after('qr_status');
            $table->timestamp('qr_rotated_at')->nullable()->after('qr_issued_at');
        });
    }

    public function down(): void
    {
        Schema::table('id_cards', function (Blueprint $table): void {
            $table->dropColumn(['public_card_uuid', 'qr_status', 'qr_issued_at', 'qr_rotated_at']);
        });
    }
};
