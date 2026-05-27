<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cafeteria_providers', function (Blueprint $table): void {
            $table->foreignUuid('service_provider_id')
                ->nullable()
                ->unique()
                ->after('id')
                ->constrained('service_providers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cafeteria_providers', function (Blueprint $table): void {
            $table->dropForeign(['service_provider_id']);
            $table->dropColumn('service_provider_id');
        });
    }
};
