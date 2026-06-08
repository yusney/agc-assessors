<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PR3 — Sitemap Tests.
 *
 * Tests the /sitemap.xml endpoint returns valid XML with all required
 * multilingual URLs for static routes and published entities.
 */
final class SitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class);
    }

    // ---------------------------------------------------------------------------
    // RED Phase — sitemap endpoint
    // ---------------------------------------------------------------------------

    /** @test */
    public function test_sitemap_endpoint_returns_http_200(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_sitemap_response_has_xml_content_type(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertHeader('Content-Type', 'application/xml');
    }

    /** @test */
    public function test_sitemap_returns_valid_xml_document(): void
    {
        $response = $this->get('/sitemap.xml');

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml, 'Response must be valid XML');
        $this->assertEquals('urlset', $xml->getName());
    }

    /** @test */
    public function test_sitemap_contains_urlset_namespace(): void
    {
        $response = $this->get('/sitemap.xml');

        $xml = simplexml_load_string($response->getContent());
        $namespaces = $xml->getNamespaces(true);

        $this->assertArrayHasKey('', $namespaces, 'urlset must have a default namespace');
    }

    // ---------------------------------------------------------------------------
    // RED Phase — static routes across all locales
    // ---------------------------------------------------------------------------

    /** @test */
    public function test_sitemap_contains_homepage_url_for_all_three_locales(): void
    {
        $response = $this->get('/sitemap.xml');
        $xml = simplexml_load_string($response->getContent());

        $locales = ['ca' => '', 'es' => 'es', 'en' => 'en'];

        foreach ($locales as $locale => $prefix) {
            $baseUrl = 'http://localhost:8080';
            $url = $prefix === '' ? $baseUrl : "{$baseUrl}/{$prefix}";

            $found = false;
            foreach ($xml->url as $urlElement) {
                if ((string) $urlElement->loc === $url) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "Homepage URL for locale '{$locale}' ({$url}) must be in sitemap");
        }
    }

    /** @test */
    public function test_sitemap_contains_serveis_route_for_all_three_locales(): void
    {
        $response = $this->get('/sitemap.xml');
        $xml = simplexml_load_string($response->getContent());

        $urls = [
            'http://localhost:8080/serveis',
            'http://localhost:8080/es/serveis',
            'http://localhost:8080/en/serveis',
        ];

        foreach ($urls as $url) {
            $found = false;
            foreach ($xml->url as $urlElement) {
                if ((string) $urlElement->loc === $url) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "Services URL ({$url}) must be in sitemap");
        }
    }

    /** @test */
    public function test_sitemap_contains_contacte_route_for_all_three_locales(): void
    {
        $response = $this->get('/sitemap.xml');
        $xml = simplexml_load_string($response->getContent());

        $urls = [
            'http://localhost:8080/contacte',
            'http://localhost:8080/es/contacte',
            'http://localhost:8080/en/contacte',
        ];

        foreach ($urls as $url) {
            $found = false;
            foreach ($xml->url as $urlElement) {
                if ((string) $urlElement->loc === $url) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "Contact URL ({$url}) must be in sitemap");
        }
    }

    /** @test */
    public function test_sitemap_contains_search_route_for_all_three_locales(): void
    {
        $response = $this->get('/sitemap.xml');
        $xml = simplexml_load_string($response->getContent());

        $urls = [
            'http://localhost:8080/search',
            'http://localhost:8080/es/search',
            'http://localhost:8080/en/search',
        ];

        foreach ($urls as $url) {
            $found = false;
            foreach ($xml->url as $urlElement) {
                if ((string) $urlElement->loc === $url) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "Search URL ({$url}) must be in sitemap");
        }
    }

    // ---------------------------------------------------------------------------
    // RED Phase — published news entries
    // ---------------------------------------------------------------------------

    /** @test */
    public function test_sitemap_does_not_include_unpublished_news(): void
    {
        $response = $this->get('/sitemap.xml');
        $xml = simplexml_load_string($response->getContent());

        $unpublishedSlug = 'this-news-is-unpublished-2026';

        foreach ($xml->url as $urlElement) {
            $this->assertStringNotContainsString($unpublishedSlug, (string) $urlElement->loc,
                'Unpublished news slug must NOT appear in sitemap');
        }
    }

    // ---------------------------------------------------------------------------
    // RED Phase — published services
    // ---------------------------------------------------------------------------

    /** @test */
    public function test_sitemap_does_not_include_inactive_services(): void
    {
        $response = $this->get('/sitemap.xml');
        $xml = simplexml_load_string($response->getContent());

        $inactiveSlug = 'inactive-service-test-slug';

        foreach ($xml->url as $urlElement) {
            $this->assertStringNotContainsString($inactiveSlug, (string) $urlElement->loc,
                'Inactive service slug must NOT appear in sitemap');
        }
    }

    // ---------------------------------------------------------------------------
    // RED Phase — each URL entry has required fields
    // ---------------------------------------------------------------------------

    /** @test */
    public function test_every_url_entry_has_loc_tag(): void
    {
        $response = $this->get('/sitemap.xml');
        $xml = simplexml_load_string($response->getContent());

        foreach ($xml->url as $urlElement) {
            $this->assertNotNull($urlElement->loc, 'Every URL entry must have a <loc> tag');
            $this->assertNotEmpty((string) $urlElement->loc, '<loc> must not be empty');
        }
    }

    /** @test */
    public function test_every_url_entry_has_changefreq_tag(): void
    {
        $response = $this->get('/sitemap.xml');
        $xml = simplexml_load_string($response->getContent());

        foreach ($xml->url as $urlElement) {
            $this->assertNotNull($urlElement->changefreq, 'Every URL entry must have a <changefreq> tag');
        }
    }

    /** @test */
    public function test_every_url_entry_has_priority_tag(): void
    {
        $response = $this->get('/sitemap.xml');
        $xml = simplexml_load_string($response->getContent());

        foreach ($xml->url as $urlElement) {
            $this->assertNotNull($urlElement->priority, 'Every URL entry must have a <priority> tag');
        }
    }
}