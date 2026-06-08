<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        // news_articles
        DB::statement('ALTER TABLE news_articles ADD COLUMN IF NOT EXISTS search_vector_ca tsvector');
        DB::statement('ALTER TABLE news_articles ADD COLUMN IF NOT EXISTS search_vector_es tsvector');
        DB::statement('ALTER TABLE news_articles ADD COLUMN IF NOT EXISTS search_vector_en tsvector');
        DB::statement('CREATE INDEX IF NOT EXISTS news_articles_search_vector_ca_idx ON news_articles USING GIN(search_vector_ca)');
        DB::statement('CREATE INDEX IF NOT EXISTS news_articles_search_vector_es_idx ON news_articles USING GIN(search_vector_es)');
        DB::statement('CREATE INDEX IF NOT EXISTS news_articles_search_vector_en_idx ON news_articles USING GIN(search_vector_en)');

        DB::statement("
            UPDATE news_articles SET
                search_vector_ca = to_tsvector('catalan',
                    coalesce(title->>'ca','') || ' ' ||
                    coalesce(excerpt->>'ca','') || ' ' ||
                    coalesce(body->>'ca','')),
                search_vector_es = to_tsvector('spanish',
                    coalesce(title->>'es','') || ' ' ||
                    coalesce(excerpt->>'es','') || ' ' ||
                    coalesce(body->>'es','')),
                search_vector_en = to_tsvector('english',
                    coalesce(title->>'en','') || ' ' ||
                    coalesce(excerpt->>'en','') || ' ' ||
                    coalesce(body->>'en',''))
        ");

        // services
        DB::statement('ALTER TABLE services ADD COLUMN IF NOT EXISTS search_vector_ca tsvector');
        DB::statement('ALTER TABLE services ADD COLUMN IF NOT EXISTS search_vector_es tsvector');
        DB::statement('ALTER TABLE services ADD COLUMN IF NOT EXISTS search_vector_en tsvector');
        DB::statement('CREATE INDEX IF NOT EXISTS services_search_vector_ca_idx ON services USING GIN(search_vector_ca)');
        DB::statement('CREATE INDEX IF NOT EXISTS services_search_vector_es_idx ON services USING GIN(search_vector_es)');
        DB::statement('CREATE INDEX IF NOT EXISTS services_search_vector_en_idx ON services USING GIN(search_vector_en)');

        DB::statement("
            UPDATE services SET
                search_vector_ca = to_tsvector('catalan',
                    coalesce(name->>'ca','') || ' ' ||
                    coalesce(description->>'ca','')),
                search_vector_es = to_tsvector('spanish',
                    coalesce(name->>'es','') || ' ' ||
                    coalesce(description->>'es','')),
                search_vector_en = to_tsvector('english',
                    coalesce(name->>'en','') || ' ' ||
                    coalesce(description->>'en',''))
        ");

        // pages
        DB::statement('ALTER TABLE pages ADD COLUMN IF NOT EXISTS search_vector_ca tsvector');
        DB::statement('ALTER TABLE pages ADD COLUMN IF NOT EXISTS search_vector_es tsvector');
        DB::statement('ALTER TABLE pages ADD COLUMN IF NOT EXISTS search_vector_en tsvector');
        DB::statement('CREATE INDEX IF NOT EXISTS pages_search_vector_ca_idx ON pages USING GIN(search_vector_ca)');
        DB::statement('CREATE INDEX IF NOT EXISTS pages_search_vector_es_idx ON pages USING GIN(search_vector_es)');
        DB::statement('CREATE INDEX IF NOT EXISTS pages_search_vector_en_idx ON pages USING GIN(search_vector_en)');

        DB::statement("
            UPDATE pages SET
                search_vector_ca = to_tsvector('catalan',
                    coalesce(title->>'ca','') || ' ' ||
                    coalesce(content->>'ca','')),
                search_vector_es = to_tsvector('spanish',
                    coalesce(title->>'es','') || ' ' ||
                    coalesce(content->>'es','')),
                search_vector_en = to_tsvector('english',
                    coalesce(title->>'en','') || ' ' ||
                    coalesce(content->>'en',''))
        ");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        Schema::table('news_articles', function ($table) {
            $table->dropColumn(['search_vector_ca', 'search_vector_es', 'search_vector_en']);
        });
        Schema::table('services', function ($table) {
            $table->dropColumn(['search_vector_ca', 'search_vector_es', 'search_vector_en']);
        });
        Schema::table('pages', function ($table) {
            $table->dropColumn(['search_vector_ca', 'search_vector_es', 'search_vector_en']);
        });
    }
};
