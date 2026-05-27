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
            $table->text('qr_payload')->nullable()->after('token_hash');
        });
    }

    public function down(): void
    {
        Schema::table('id_cards', function (Blueprint $table): void {
            $table->dropColumn('qr_payload');
        });
    }
};
