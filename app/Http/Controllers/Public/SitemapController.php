<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use AGC\Infrastructure\Persistence\Eloquent\Models\NewsModel;
use AGC\Infrastructure\Persistence\Eloquent\Models\PageModel;
use AGC\Infrastructure\Persistence\Eloquent\Models\ServiceModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

final class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];

        // Static routes × 3 locales
        $staticRoutes = [
            '/' => 'home',
            '/serveis' => 'services.index',
            '/oficines' => 'offices.index',
            '/equip' => 'team',
            '/contacte' => 'contact',
            '/search' => 'search',
        ];

        foreach ($staticRoutes as $route => $name) {
            foreach (['ca', 'es', 'en'] as $locale) {
                $urls[] = $this->buildUrlEntry(
                    $this->localizedUrl($route, $locale),
                    $this->priorityForStaticRoute($name, $locale),
                    $this->changeFrequencyForStaticRoute($name)
                );
            }
        }

        // Published News articles × 3 locales
        $newsArticles = NewsModel::query()
            ->where('published', true)
            ->get();

        foreach ($newsArticles as $article) {
            foreach (['ca', 'es', 'en'] as $locale) {
                $urls[] = $this->buildUrlEntry(
                    $this->localizedUrl('/actualitat/' . $article->slug, $locale),
                    '0.8',
                    'weekly'
                );
            }
        }

        // Published Pages × 3 locales
        $pages = PageModel::query()
            ->where('published', true)
            ->get();

        foreach ($pages as $page) {
            foreach (['ca', 'es', 'en'] as $locale) {
                $urls[] = $this->buildUrlEntry(
                    $this->localizedUrl('/pages/' . $page->slug, $locale),
                    '0.7',
                    'monthly'
                );
            }
        }

        // Active Services × 3 locales
        $services = ServiceModel::query()
            ->where('active', true)
            ->get();

        foreach ($services as $service) {
            foreach (['ca', 'es', 'en'] as $locale) {
                $urls[] = $this->buildUrlEntry(
                    $this->localizedUrl('/serveis/' . $service->slug, $locale),
                    '0.8',
                    'weekly'
                );
            }
        }

        $xml = $this->generateXml($urls);

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    private function localizedUrl(string $path, string $locale): string
    {
        $url = LaravelLocalization::getLocalizedURL($locale, $path, [], false);

        return rtrim($url, '/');
    }

    private function buildUrlEntry(string $url, string $priority, string $changeFreq): string
    {
        return <<<XML
    <url>
        <loc>{$url}</loc>
        <changefreq>{$changeFreq}</changefreq>
        <priority>{$priority}</priority>
    </url>
XML;
    }

    private function generateXml(array $urls): string
    {
        $urlElements = implode("\n", $urls);

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
{$urlElements}
</urlset>
XML;
    }

    private function priorityForStaticRoute(string $route, string $locale): string
    {
        if ($route === 'home') {
            return $locale === 'ca' ? '1.0' : '0.9';
        }

        return '0.7';
    }

    private function changeFrequencyForStaticRoute(string $route): string
    {
        return match ($route) {
            'home', 'services.index' => 'daily',
            'contact', 'search' => 'weekly',
            default => 'monthly',
        };
    }
}
