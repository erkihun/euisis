<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('profile_photo_path')->nullable()->after('employee_reference');
            $table->string('national_id')->nullable()->unique()->after('profile_photo_path');
            $table->string('phone_number', 30)->nullable()->after('national_id');
            $table->string('gender')->nullable()->after('phone_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['profile_photo_path', 'national_id', 'phone_number', 'gender']);
        });
    }
};
