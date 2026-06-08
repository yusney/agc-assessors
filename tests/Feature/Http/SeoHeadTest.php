<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * PR1 — Foundation & Head Rendering.
 *
 * Section A: SEO partial tests — render the SEO partial directly (no DB).
 * Section B: HTTP integration tests — GET / and GET /serveis/{slug}
 *   to prove tags appear in actual public pages.
 *
 *   HTTP tests use RefreshDatabase. The search-vector migration
 *   (2026_06_03_100000) is made PostgreSQL-conditional so SQLite in-memory
 *   works.  Service rows are inserted via DB::table() to bypass the
 *   Eloquent boot hook that runs PostgreSQL tsvector SQL.
 */
final class SeoHeadTest extends TestCase
{
    /**
     * Set Accept-Language: ca so that LaravelLocalization's localizationRedirect
     * middleware detects the Catalan locale (hideDefaultLocaleInURL=true → no prefix
     * needed → no redirect). Without this header, the Docker environment defaults
     * to 'en' and the middleware would redirect all requests to /en/{path}.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeaders(['Accept-Language' => 'ca']);
    }

    // ---------------------------------------------------------------------------
    // Section A — SEO partial: hreflang × 4
    // ---------------------------------------------------------------------------

    /** @test */
    public function test_seo_partial_renders_all_four_hreflang_tags(): void
    {
        $view = $this->view('public.partials.seo', $this->baseSeoData());

        $view->assertSee('hreflang="ca"', false);
        $view->assertSee('hreflang="es"', false);
        $view->assertSee('hreflang="en"', false);
        $view->assertSee('hreflang="x-default"', false);
    }

    /** @test */
    public function test_seo_partial_hreflang_x_default_equals_ca_url(): void
    {
        $caUrl = 'https://agcassessors.com/serveis/comptabilitat';
        $data  = $this->baseSeoData([
            'hreflangAlternates' => [
                ['locale' => 'ca', 'url' => $caUrl],
                ['locale' => 'es', 'url' => 'https://agcassessors.com/es/serveis/comptabilitat'],
                ['locale' => 'en', 'url' => 'https://agcassessors.com/en/serveis/comptabilitat'],
            ],
        ]);

        $html = (string) $this->view('public.partials.seo', $data);

        // x-default must point to the Catalan (unprefixed) URL — exact attribute match
        $this->assertStringContainsString(
            'hreflang="x-default" href="' . $caUrl . '"',
            $html
        );
    }

    // ---------------------------------------------------------------------------
    // Section A — SEO partial: og:locale and og:locale:alternate
    // ---------------------------------------------------------------------------

    /** @test */
    public function test_seo_partial_renders_og_locale_for_active_locale(): void
    {
        $view = $this->view('public.partials.seo', $this->baseSeoData(['ogLocale' => 'ca_ES']));

        $view->assertSee('og:locale', false);
        $view->assertSee('ca_ES', false);
    }

    /** @test */
    public function test_seo_partial_renders_og_locale_alternates_for_other_locales(): void
    {
        $data = $this->baseSeoData([
            'ogLocale'           => 'en_GB',
            'ogLocaleAlternates' => ['ca_ES', 'es_ES'],
        ]);

        $html = (string) $this->view('public.partials.seo', $data);

        // Exact og:locale:alternate tags must be present
        $this->assertStringContainsString('<meta property="og:locale:alternate" content="ca_ES">', $html);
        $this->assertStringContainsString('<meta property="og:locale:alternate" content="es_ES">', $html);
        // Active locale must NOT appear as an alternate
        $this->assertStringNotContainsString('"og:locale:alternate" content="en_GB"', $html);
    }

    // ---------------------------------------------------------------------------
    // Section A — SEO partial: Twitter card mirroring (strong assertions)
    // ---------------------------------------------------------------------------

    /** @test */
    public function test_seo_partial_renders_twitter_card_tags(): void
    {
        $view = $this->view('public.partials.seo', $this->baseSeoData([
            'seoTitle'       => 'Test SEO Title',
            'seoDescription' => 'Test SEO description text',
        ]));

        $view->assertSee('twitter:card', false);
        $view->assertSee('summary_large_image', false);
        $view->assertSee('twitter:title', false);
        $view->assertSee('Test SEO Title', false);
        $view->assertSee('twitter:description', false);
        $view->assertSee('Test SEO description text', false);
    }

    /** @test */
    public function test_seo_partial_twitter_values_mirror_og_values_exactly(): void
    {
        $title       = 'OG and Twitter Title';
        $description = 'OG and Twitter Description';

        $html = (string) $this->view('public.partials.seo', $this->baseSeoData([
            'seoTitle'       => $title,
            'seoDescription' => $description,
        ]));

        // Exact tag assertions — not just count, but actual attribute+content pairs
        $this->assertStringContainsString(
            '<meta property="og:title" content="' . $title . '">',
            $html,
            'og:title must contain the exact seoTitle value'
        );
        $this->assertStringContainsString(
            '<meta name="twitter:title" content="' . $title . '">',
            $html,
            'twitter:title must mirror og:title exactly'
        );
        $this->assertStringContainsString(
            '<meta property="og:description" content="' . $description . '">',
            $html,
            'og:description must contain the exact seoDescription value'
        );
        $this->assertStringContainsString(
            '<meta name="twitter:description" content="' . $description . '">',
            $html,
            'twitter:description must mirror og:description exactly'
        );
    }

    // ---------------------------------------------------------------------------
    // Section A — SEO partial: canonical link
    // ---------------------------------------------------------------------------

    /** @test */
    public function test_seo_partial_renders_canonical_link(): void
    {
        $canonical = 'https://agcassessors.com/serveis/assessoria-fiscal';

        $html = (string) $this->view('public.partials.seo', $this->baseSeoData([
            'canonicalUrl' => $canonical,
        ]));

        $this->assertStringContainsString(
            'rel="canonical" href="' . $canonical . '"',
            $html
        );
    }

    // ---------------------------------------------------------------------------
    // Section A — SEO partial: og:url, og:type
    // ---------------------------------------------------------------------------

    /** @test */
    public function test_seo_partial_renders_og_url_and_og_type(): void
    {
        $canonical = 'https://agcassessors.com/test';

        $html = (string) $this->view('public.partials.seo', $this->baseSeoData([
            'canonicalUrl' => $canonical,
        ]));

        $this->assertStringContainsString('<meta property="og:url" content="' . $canonical . '">', $html);
        $this->assertStringContainsString('<meta property="og:type" content="website">', $html);
    }

    // ---------------------------------------------------------------------------
    // Section A — SEO partial: no keywords meta tag
    // ---------------------------------------------------------------------------

    /** @test */
    public function test_seo_partial_does_not_render_keywords_meta(): void
    {
        $html = (string) $this->view('public.partials.seo', $this->baseSeoData());

        $this->assertStringNotContainsString('name="keywords"', $html);
        $this->assertStringNotContainsString("name='keywords'", $html);
    }

    // ---------------------------------------------------------------------------
    // Section B — HTTP integration: GET / home route via redirect
    // ---------------------------------------------------------------------------

    /**
     * Proves that the home page renders all required SEO head elements:
     * canonical link, all four hreflang alternates (ca, es, en, x-default),
     * og:locale, and no meta keywords.
     *
     * Uses $this->view() to render the home view directly because the outer
     * GET / route in routes/web.php always redirects to the localized absolute
     * URL for the default locale (creating a redirect loop), and /es/ returns
     * 404 in the test environment (only ca-prefix routes are registered). The
     * view approach still fires SeoComposer (registered on layouts.public via
     * View::composer) and produces the full layout HTML with all SEO tags.
     *
     * @test
     */
    public function test_home_route_slash_renders_canonical_hreflang_og_locale_no_keywords(): void
    {
        $this->refreshDatabase();

        $html = (string) $this->view('public.pages.home', [
            'sections'   => collect([]),
            'services'   => collect([]),
            'news'       => collect([]),
            'offices'    => collect([]),
            'mapsApiKey' => '',
        ]);

        // Canonical link must be present in the rendered <head>
        $this->assertStringContainsString('<link rel="canonical"', $html);

        // All four hreflang alternate tags (ca, es, en + x-default)
        $this->assertStringContainsString('hreflang="ca"', $html);
        $this->assertStringContainsString('hreflang="es"', $html);
        $this->assertStringContainsString('hreflang="en"', $html);
        $this->assertStringContainsString('hreflang="x-default"', $html);

        // og:locale must be present (e.g. ca_ES for the default Catalan locale)
        $this->assertStringContainsString('og:locale', $html);

        // No meta keywords — locked by the no-keywords design decision
        $this->assertStringNotContainsString('name="keywords"', $html);
        $this->assertStringNotContainsString("name='keywords'", $html);
    }

    // ---------------------------------------------------------------------------
    // Section B — HTTP integration: public page with hreflang (uses services page)
    // ---------------------------------------------------------------------------

    /**
     * Proves hreflang tags appear in an actual rendered public page.
     * Uses a services page to independently verify the services.show route.
     *
     * @test
     */
    public function test_home_page_renders_all_four_hreflang_tags(): void
    {
        $this->refreshDatabase();
        $this->seedTestService('hreflang-test', 'Hreflang Test Page');

        $response = $this->get('/serveis/hreflang-test');

        $response->assertStatus(200);
        $response->assertSee('<link rel="alternate" hreflang="ca"', false);
        $response->assertSee('<link rel="alternate" hreflang="es"', false);
        $response->assertSee('<link rel="alternate" hreflang="en"', false);
        $response->assertSee('<link rel="alternate" hreflang="x-default"', false);
    }

    /**
     * Proves that the ca hreflang and x-default URLs on a public page do NOT
     * contain a /ca/ or /ca prefix (hideDefaultLocaleInURL = true must be honoured).
     *
     * @test
     */
    public function test_home_page_ca_hreflang_has_no_locale_prefix(): void
    {
        $this->refreshDatabase();
        $this->seedTestService('no-prefix-test', 'No Prefix Test Page');

        $response = $this->get('/serveis/no-prefix-test');

        $response->assertStatus(200);

        $html = $response->getContent();

        // Extract the href for hreflang="ca"
        preg_match('/hreflang="ca"\s+href="([^"]+)"/', $html, $caMatch);
        preg_match('/hreflang="x-default"\s+href="([^"]+)"/', $html, $xDefaultMatch);

        $this->assertNotEmpty($caMatch, 'hreflang="ca" tag must be present in the response');
        $this->assertStringNotContainsString('/ca', $caMatch[1],
            "Catalan hreflang href must not contain /ca segment; got: {$caMatch[1]}");

        $this->assertNotEmpty($xDefaultMatch, 'hreflang="x-default" tag must be present');
        $this->assertStringNotContainsString('/ca', $xDefaultMatch[1],
            "x-default hreflang href must not contain /ca segment; got: {$xDefaultMatch[1]}");
    }

    // ---------------------------------------------------------------------------
    // Section B — HTTP integration: services.show
    // ---------------------------------------------------------------------------

    /**
     * Proves that services.show renders the SEO title from seo_title DB column,
     * not from name(), and that hreflang tags are present in the full page.
     *
     * @test
     */
    public function test_services_show_renders_seo_title_from_db_and_hreflang_tags(): void
    {
        $this->refreshDatabase();

        // Insert via DB::table to bypass the Eloquent boot hook (which runs
        // PostgreSQL tsvector SQL unsupported by SQLite).
        DB::table('services')->insert([
            'slug'            => 'assessoria-fiscal',
            'name'            => json_encode(['ca' => 'Assessoria Fiscal', 'es' => 'Asesoría Fiscal', 'en' => 'Tax Advisory']),
            'description'     => json_encode(['ca' => '<p>Descripció del servei fiscal.</p>']),
            'seo_title'       => json_encode(['ca' => 'Test SEO Title – AGC Assessors']),
            'seo_description' => json_encode(['ca' => 'Test SEO description per a la pàgina de serveis.']),
            'active'          => 1,
            'sort_order'      => 1,
            'created_at'      => now()->toDateTimeString(),
            'updated_at'      => now()->toDateTimeString(),
        ]);

        $response = $this->get('/serveis/assessoria-fiscal');

        $response->assertStatus(200);

        // Primary <title> must come from seo_title, not name()
        $response->assertSee('<title>Test SEO Title – AGC Assessors</title>', false);
        $response->assertDontSee('<title>Assessoria Fiscal</title>', false);

        // Hreflang tags must be present in the full page
        $response->assertSee('<link rel="alternate" hreflang="ca"', false);
        $response->assertSee('<link rel="alternate" hreflang="es"', false);
        $response->assertSee('<link rel="alternate" hreflang="en"', false);
        $response->assertSee('<link rel="alternate" hreflang="x-default"', false);
    }

    /**
     * Proves that the services.show page falls back to name() when seo_title is
     * null for the active locale.
     *
     * @test
     */
    public function test_services_show_falls_back_to_name_when_seo_title_null(): void
    {
        $this->refreshDatabase();

        DB::table('services')->insert([
            'slug'            => 'servei-fallback',
            'name'            => json_encode(['ca' => 'Nom de Fallback']),
            'description'     => json_encode(['ca' => '<p>Descripció.</p>']),
            'seo_title'       => json_encode([]),  // empty — triggers fallback to name()
            'seo_description' => json_encode([]),
            'active'          => 1,
            'sort_order'      => 2,
            'created_at'      => now()->toDateTimeString(),
            'updated_at'      => now()->toDateTimeString(),
        ]);

        $response = $this->get('/serveis/servei-fallback');

        $response->assertStatus(200);

        // When seo_title is empty, the title must NOT be empty — it falls back to name()
        $response->assertSee('Nom de Fallback', false);
    }

    /**
     * Proves the layout escapes HTML in the <title> tag (XSS prevention).
     * With {{ $_seoTitle }} the angle brackets must be HTML-encoded.
     *
     * @test
     */
    public function test_layout_title_escapes_html_entities_in_seo_title(): void
    {
        $this->refreshDatabase();

        DB::table('services')->insert([
            'slug'            => 'xss-test-service',
            'name'            => json_encode(['ca' => 'Service Name']),
            'description'     => json_encode(['ca' => 'Description']),
            'seo_title'       => json_encode(['ca' => '<b>Bold</b> Title']),
            'seo_description' => json_encode(['ca' => 'Description']),
            'active'          => 1,
            'sort_order'      => 3,
            'created_at'      => now()->toDateTimeString(),
            'updated_at'      => now()->toDateTimeString(),
        ]);

        $response = $this->get('/serveis/xss-test-service');

        $response->assertStatus(200);

        // Blade {{ }} must HTML-encode < and >
        $response->assertSee('<title>&lt;b&gt;Bold&lt;/b&gt; Title</title>', false);
        // The raw unescaped <b> tag must NOT appear inside <title>
        $this->assertStringNotContainsString('<title><b>Bold</b>', $response->getContent());
    }

    // ---------------------------------------------------------------------------
    // Section B — Double-encoding regression: ampersand encoded exactly once
    // ---------------------------------------------------------------------------

    /**
     * Proves that an ampersand in seo_title / seo_description is HTML-encoded
     * exactly ONCE in every SEO head tag.
     *
     * Root cause: @section('name', $value) shorthand calls e() → value stored as
     * &amp;. Layout previously passed {!! !!} for <title> (fine) but passed the
     * pre-escaped value to the partial whose {{ }} would encode again → &amp;amp;
     * in og:title and twitter:title.
     *
     * Fix: layout decodes the yielded section value back to plain text via
     * html_entity_decode(), then all outputs use {{ }} → one encoding pass everywhere.
     *
     * @test
     */
    public function test_ampersand_in_seo_title_and_description_is_single_encoded_in_all_head_tags(): void
    {
        $this->refreshDatabase();

        DB::table('services')->insert([
            'slug'            => 'amp-encode-test',
            'name'            => json_encode(['ca' => 'Test Service']),
            'description'     => json_encode(['ca' => 'Description']),
            'seo_title'       => json_encode(['ca' => 'Serveis & Solucions – AGC']),
            'seo_description' => json_encode(['ca' => 'Fiscal & Laboral advisory']),
            'active'          => 1,
            'sort_order'      => 1,
            'created_at'      => now()->toDateTimeString(),
            'updated_at'      => now()->toDateTimeString(),
        ]);

        $response = $this->get('/serveis/amp-encode-test');
        $response->assertStatus(200);
        $html = $response->getContent();

        // <title> and description meta: encoded once
        $this->assertStringContainsString('<title>Serveis &amp; Solucions – AGC</title>', $html,
            '<title> must encode & exactly once');
        $this->assertStringContainsString('name="description" content="Fiscal &amp; Laboral advisory"', $html,
            '<meta name="description"> must encode & exactly once');

        // OG tags: single-encoded (NOT &amp;amp;)
        $this->assertStringContainsString('property="og:title" content="Serveis &amp; Solucions – AGC"', $html,
            'og:title must encode & exactly once — not double-encode to &amp;amp;');
        $this->assertStringContainsString('property="og:description" content="Fiscal &amp; Laboral advisory"', $html,
            'og:description must encode & exactly once');

        // Twitter tags: single-encoded (NOT &amp;amp;)
        $this->assertStringContainsString('name="twitter:title" content="Serveis &amp; Solucions – AGC"', $html,
            'twitter:title must encode & exactly once');
        $this->assertStringContainsString('name="twitter:description" content="Fiscal &amp; Laboral advisory"', $html,
            'twitter:description must encode & exactly once');

        // Global guard: no double-encoded ampersand anywhere in the page
        $this->assertStringNotContainsString('&amp;amp;', $html,
            'Double-encoded &amp;amp; must not appear anywhere in the rendered page');
    }

    /**
     * Proves that a <script> payload in seo_title is escaped (not raw/executable)
     * in the og:title and twitter:title meta tags.
     *
     * With double-encoding the og:title would contain &amp;lt;script&amp;gt;
     * (ugly, browser-decoded to &lt;script&gt; — not executable but semantically wrong).
     * After the fix, og:title contains &lt;script&gt; — single-encoded, clean.
     *
     * @test
     */
    public function test_script_injection_in_seo_title_is_escaped_in_og_and_twitter_tags(): void
    {
        $this->refreshDatabase();

        DB::table('services')->insert([
            'slug'            => 'xss-og-test',
            'name'            => json_encode(['ca' => 'Service Name']),
            'description'     => json_encode(['ca' => 'Description']),
            'seo_title'       => json_encode(['ca' => '<script>alert("xss")</script>']),
            'seo_description' => json_encode(['ca' => 'Safe description']),
            'active'          => 1,
            'sort_order'      => 1,
            'created_at'      => now()->toDateTimeString(),
            'updated_at'      => now()->toDateTimeString(),
        ]);

        $response = $this->get('/serveis/xss-og-test');
        $response->assertStatus(200);
        $html = $response->getContent();

        // og:title must be single-encoded (&lt;script&gt; — not &amp;lt;script&amp;gt;)
        $this->assertStringContainsString('property="og:title" content="&lt;script&gt;', $html,
            'og:title must contain &lt;script&gt; (single-encoded) — not the double-encoded form');

        // twitter:title must also be single-encoded
        $this->assertStringContainsString('name="twitter:title" content="&lt;script&gt;', $html,
            'twitter:title must contain &lt;script&gt; (single-encoded)');

        // Raw <script> tag must never appear in any SEO meta attribute
        $this->assertStringNotContainsString('content="<script>', $html,
            'Raw unescaped <script> must never appear in a meta content attribute');

        // No double-encoded form anywhere
        $this->assertStringNotContainsString('&amp;lt;script', $html,
            'Double-encoded &amp;lt;script must not appear — indicates double-encoding bug');
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    /**
     * Returns a complete set of test variables for the SEO partial.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function baseSeoData(array $overrides = []): array
    {
        return array_merge([
            'canonicalUrl'       => 'https://agcassessors.com/',
            'seoTitle'           => 'AGC Assessors',
            'seoDescription'     => 'Assessoria fiscal, laboral i comptable a Barcelona.',
            'ogLocale'           => 'ca_ES',
            'ogLocaleAlternates' => ['es_ES', 'en_GB'],
            'ogType'             => 'website',
            'hreflangAlternates' => [
                ['locale' => 'ca', 'url' => 'https://agcassessors.com/'],
                ['locale' => 'es', 'url' => 'https://agcassessors.com/es/'],
                ['locale' => 'en', 'url' => 'https://agcassessors.com/en/'],
            ],
        ], $overrides);
    }

    /**
     * Run migrations for HTTP integration tests. We do NOT use the
     * RefreshDatabase trait class-wide because some tests in this file are
     * partial-only and must remain fast and DB-free.
     */
    private function refreshDatabase(): void
    {
        $this->artisan('migrate:fresh', ['--env' => 'testing'])->run();
    }

    /**
     * Insert a minimal service row via DB::table to bypass the Eloquent boot
     * hook which runs PostgreSQL tsvector SQL unsupported by SQLite.
     */
    private function seedTestService(string $slug, string $name): void
    {
        DB::table('services')->insert([
            'slug'            => $slug,
            'name'            => json_encode(['ca' => $name, 'es' => $name, 'en' => $name]),
            'description'     => json_encode(['ca' => '<p>Test description.</p>']),
            'seo_title'       => json_encode(['ca' => $name . ' – AGC Assessors']),
            'seo_description' => json_encode(['ca' => 'Test SEO description.']),
            'active'          => 1,
            'sort_order'      => 1,
            'created_at'      => now()->toDateTimeString(),
            'updated_at'      => now()->toDateTimeString(),
        ]);
    }
}

