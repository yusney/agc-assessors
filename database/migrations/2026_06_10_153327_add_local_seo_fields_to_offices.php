<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table): void {
            $table->json('opening_hours')->nullable()->after('email');
            $table->json('service_area')->nullable()->after('opening_hours');
            $table->json('image_alt')->nullable()->after('service_area');
        });
    }

    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table): void {
            $table->dropColumn(['opening_hours', 'service_area', 'image_alt']);
        });
    }
};
