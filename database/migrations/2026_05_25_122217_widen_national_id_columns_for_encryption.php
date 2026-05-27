<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropUnique(['national_id']);
            $table->text('national_id')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['national_id']);
            $table->text('national_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->string('national_id', 16)->nullable()->change();
            $table->unique('national_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('national_id')->nullable()->change();
            $table->unique('national_id');
        });
    }
};
