<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table): void {
            $table->unsignedBigInteger('cover_media_id')->nullable()->after('id');
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->unsignedBigInteger('cover_media_id')->nullable()->after('id');
        });

        Schema::table('team_members', function (Blueprint $table): void {
            $table->unsignedBigInteger('photo_media_id')->nullable()->after('id');
        });

        Schema::table('home_sections', function (Blueprint $table): void {
            $table->unsignedBigInteger('main_image_media_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('news_articles', function (Blueprint $table): void {
            $table->dropColumn('cover_media_id');
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn('cover_media_id');
        });

        Schema::table('team_members', function (Blueprint $table): void {
            $table->dropColumn('photo_media_id');
        });

        Schema::table('home_sections', function (Blueprint $table): void {
            $table->dropColumn('main_image_media_id');
        });
    }
};
