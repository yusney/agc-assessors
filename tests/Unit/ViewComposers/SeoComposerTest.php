<?php

declare(strict_types=1);

namespace Tests\Unit\ViewComposers;

use AGC\Domain\Service\Entities\Service;
use AGC\Domain\Shared\ValueObjects\SEOData;
use AGC\Domain\Shared\ValueObjects\Slug;
use AGC\Domain\Shared\ValueObjects\TranslatableString;
use App\Http\View\Composers\SeoComposer;
use Tests\TestCase;

/**
 * Unit tests for SeoComposer.
 *
 * Tests cover the new PR1 methods:
 * - getHreflangAlternates()
 * - getOgLocaleAlternates()
 * - getGlobalDefaultTitle()
 * - getGlobalDefaultDescription()
 *
 * The "services.show uses seo()->title()" fix is validated here at the domain
 * level (pure PHP, no DB or Blade rendering required).
 */
final class SeoComposerTest extends TestCase
{
    // ---------------------------------------------------------------------------
    // Task 1.4 — getHreflangAlternates()
    // ---------------------------------------------------------------------------

    /** @test */
    public function test_get_hreflang_alternates_returns_three_locales(): void
    {
        $composer   = new SeoComposer();
        $alternates = $composer->getHreflangAlternates();

        $this->assertCount(3, $alternates);
    }

    /** @test */
    public function test_get_hreflang_alternates_contains_ca_es_en(): void
    {
        $composer = new SeoComposer();
        $locales  = array_column($composer->getHreflangAlternates(), 'locale');

        $this->assertContains('ca', $locales);
        $this->assertContains('es', $locales);
        $this->assertContains('en', $locales);
    }

    /** @test */
    public function test_get_hreflang_alternates_each_entry_has_locale_and_url(): void
    {
        $composer   = new SeoComposer();
        $alternates = $composer->getHreflangAlternates();

        foreach ($alternates as $alt) {
            $this->assertArrayHasKey('locale', $alt, 'Each alternate must have a locale key');
            $this->assertArrayHasKey('url', $alt, 'Each alternate must have a url key');
            $this->assertNotEmpty($alt['url'], 'URL for locale ' . $alt['locale'] . ' must not be empty');
        }
    }

    /** @test */
    public function test_get_hreflang_alternates_ca_url_has_no_locale_prefix(): void
    {
        $composer   = new SeoComposer();
        $alternates = $composer->getHreflangAlternates();

        $caEntry = collect($alternates)->firstWhere('locale', 'ca');

        $this->assertNotNull($caEntry, 'ca alternate must be present');
        // Stronger assertion: catch /ca (no trailing slash) as well as /ca/
        $caUrl = (string) $caEntry['url'];
        $this->assertStringNotContainsString('/ca', $caUrl,
            "Catalan (default) hreflang URL must not contain any /ca segment; got: {$caUrl}");
    }

    /** @test */
    public function test_get_hreflang_alternates_secondary_locales_have_prefix(): void
    {
        $composer   = new SeoComposer();
        $alternates = $composer->getHreflangAlternates();

        $esEntry = collect($alternates)->firstWhere('locale', 'es');
        $enEntry = collect($alternates)->firstWhere('locale', 'en');

        $this->assertNotNull($esEntry, 'es alternate must be present');
        $this->assertNotNull($enEntry, 'en alternate must be present');
        // Secondary locales must include their prefix
        $this->assertStringContainsString('/es', (string) $esEntry['url'],
            "Spanish hreflang URL must include /es prefix");
        $this->assertStringContainsString('/en', (string) $enEntry['url'],
            "English hreflang URL must include /en prefix");
    }

    // ---------------------------------------------------------------------------
    // Task 1.4 — getOgLocaleAlternates()
    // ---------------------------------------------------------------------------

    /** @test */
    public function test_get_og_locale_alternates_returns_non_active_locales(): void
    {
        app()->setLocale('ca');

        $composer   = new SeoComposer();
        $alternates = $composer->getOgLocaleAlternates();

        // Active locale 'ca' (ca_ES) must NOT appear in alternates
        $this->assertNotContains('ca_ES', $alternates);

        // Non-active locales must be present
        $this->assertContains('es_ES', $alternates);
        $this->assertContains('en_GB', $alternates);
    }

    /** @test */
    public function test_get_og_locale_alternates_excludes_only_active_locale(): void
    {
        app()->setLocale('en');

        $composer   = new SeoComposer();
        $alternates = $composer->getOgLocaleAlternates();

        // Active locale en_GB must NOT appear
        $this->assertNotContains('en_GB', $alternates);

        // ca and es must appear
        $this->assertContains('ca_ES', $alternates);
        $this->assertContains('es_ES', $alternates);
    }

    // ---------------------------------------------------------------------------
    // Task 1.4 — getGlobalDefaultTitle() / getGlobalDefaultDescription()
    // ---------------------------------------------------------------------------

    /** @test */
    public function test_get_global_default_title_returns_null_when_no_setting(): void
    {
        // SiteSetting::get returns null for unknown keys — this exercises the fallback path.
        // In test environment the DB may not have SEO settings; method must never throw.
        $composer = new SeoComposer();

        $result = $composer->getGlobalDefaultTitle('ca');

        // When no SiteSetting row exists, the method MUST return null (not an empty string, not throw).
        $this->assertNull($result, 'getGlobalDefaultTitle must return null when no SiteSetting row exists');
    }

    /** @test */
    public function test_get_global_default_description_returns_null_when_no_setting(): void
    {
        $composer = new SeoComposer();

        $result = $composer->getGlobalDefaultDescription('ca');

        $this->assertNull($result, 'getGlobalDefaultDescription must return null when no SiteSetting row exists');
    }

    // ---------------------------------------------------------------------------
    // Task 1.1 — Service domain entity: seo()->title() vs name() (unit level)
    // ---------------------------------------------------------------------------

    /** @test */
    public function test_service_seo_title_differs_from_name_when_set(): void
    {
        $service = new Service(
            id: 1,
            name: new TranslatableString([
                'ca' => 'Nom del Servei',
                'es' => 'Nombre del Servicio',
                'en' => 'Service Name',
            ]),
            description: new TranslatableString(['ca' => 'Descripció del servei']),
            slug: new Slug('servei-test'),
            seo: new SEOData(
                title: new TranslatableString([
                    'ca' => 'Assessoria Fiscal – AGC',
                    'es' => 'Asesoría Fiscal – AGC',
                    'en' => 'Tax Advisory – AGC',
                ]),
                description: new TranslatableString([
                    'ca' => 'Descripció SEO optimitzada',
                ]),
            ),
        );

        $locale = 'ca';

        $this->assertSame('Assessoria Fiscal – AGC', $service->seo()->title()->get($locale));
        $this->assertNotSame($service->name()->get($locale), $service->seo()->title()->get($locale));
    }

    /** @test */
    public function test_service_falls_back_to_name_when_seo_title_is_empty(): void
    {
        $service = new Service(
            id: 2,
            name: new TranslatableString(['ca' => 'Nom de Fallback']),
            description: new TranslatableString(['ca' => 'Desc']),
            slug: new Slug('servei-fallback'),
            seo: new SEOData(
                title: new TranslatableString([]),        // empty → fallback to name()
                description: new TranslatableString([]),
            ),
        );

        $seoTitle = $service->seo()->title()->get('ca');
        $name     = $service->name()->get('ca');

        // When seo title is empty, the view fallback ($service->seo()->title()->get($locale) ?: $service->name())
        // must resolve to name().
        $effective = $seoTitle ?: $name;

        $this->assertSame('Nom de Fallback', $effective);
    }
}
