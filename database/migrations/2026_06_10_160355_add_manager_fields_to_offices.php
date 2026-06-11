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
            $table->json('manager_name')->nullable()->after('slug');
            $table->json('manager_role')->nullable()->after('manager_name');
            $table->json('manager_bio')->nullable()->after('manager_role');
        });
    }

    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table): void {
            $table->dropColumn(['manager_name', 'manager_role', 'manager_bio']);
        });
    }
};
