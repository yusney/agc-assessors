<?php

declare(strict_types=1);

namespace App\Http\View\Composers;

use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use Illuminate\View\View;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

final class SeoComposer
{
    public function compose(View $view): void
    {
        $schemas = [];

        $organization = $this->getOrganizationSchema();
        if ($organization !== []) {
            $schemas[] = $organization;
        }

        $localBusiness = $this->getLocalBusinessSchema();
        if ($localBusiness !== []) {
            $schemas[] = $localBusiness;
        }

        $website = $this->getWebsiteSchema();
        if ($website !== []) {
            $schemas[] = $website;
        }

        $breadcrumbs = $view->getData()['breadcrumbs'] ?? [];
        if ($breadcrumbs !== []) {
            $breadcrumbSchema = $this->getBreadcrumbSchema($breadcrumbs);
            if ($breadcrumbSchema !== []) {
                $schemas[] = $breadcrumbSchema;
            }
        }

        $view->with('schemas', $schemas);
        $view->with('ogImage', $this->getOgImage());
        $view->with('canonicalUrl', $this->getCanonicalUrl());
        $view->with('hreflangAlternates', $this->getHreflangAlternates());
        $view->with('ogLocaleAlternates', $this->getOgLocaleAlternates());
        $view->with('ogLocale', $this->getActiveOgLocale());
        $view->with('globalDefaultTitle', $this->getGlobalDefaultTitle());
        $view->with('globalDefaultDescription', $this->getGlobalDefaultDescription());
    }

    private function getCanonicalUrl(): string
    {
        return LaravelLocalization::getLocalizedURL(
            app()->getLocale(),
            url()->current(),
            []
        );
    }

    /**
     * Returns hreflang alternate entries for all supported locales.
     *
     * @return array<int, array{locale: string, url: string}>
     */
    public function getHreflangAlternates(): array
    {
        $alternates = [];

        foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
            $alternates[] = [
                'locale' => (string) $locale,
                'url'    => LaravelLocalization::getLocalizedURL(
                    (string) $locale,
                    url()->current(),
                    []
                ),
            ];
        }

        return $alternates;
    }

    /**
     * Returns regional locale codes (e.g. 'es_ES') for all locales EXCEPT the
     * currently active one — used for og:locale:alternate meta tags.
     *
     * @return array<int, string>
     */
    public function getOgLocaleAlternates(): array
    {
        $activeLocale = app()->getLocale();
        $alternates   = [];

        foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
            if ($locale !== $activeLocale) {
                $regional = $properties['regional'] ?? $locale;
                if (is_string($regional) && $regional !== '') {
                    $alternates[] = $regional;
                }
            }
        }

        return $alternates;
    }

    /**
     * Returns the regional locale code for the currently active locale,
     * e.g. 'ca_ES', 'es_ES', 'en_GB'.
     */
    public function getActiveOgLocale(): string
    {
        $locale     = app()->getLocale();
        $locales    = LaravelLocalization::getSupportedLocales();
        $properties = $locales[$locale] ?? [];
        $regional   = $properties['regional'] ?? $locale;

        return is_string($regional) && $regional !== '' ? $regional : $locale;
    }

    /**
     * Returns the global default SEO title for a given locale from SiteSetting,
     * or null when none is configured.
     */
    public function getGlobalDefaultTitle(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        try {
            $value = SiteSetting::get("seo.global.{$locale}.title");
        } catch (\Throwable) {
            return null;
        }

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * Returns the global default SEO description for a given locale from
     * SiteSetting, or null when none is configured.
     */
    public function getGlobalDefaultDescription(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        try {
            $value = SiteSetting::get("seo.global.{$locale}.description");
        } catch (\Throwable) {
            return null;
        }

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function getOrganizationSchema(): array
    {
        /** @var array<string,mixed>|null $contact */
        $contact = SiteSetting::get('contact');

        $name = SiteSetting::get('site_name', config('app.name', 'AGC Assessors'));
        $url = config('app.url', 'https://agcassessors.com');
        $logo = $this->getLogoUrl();
        $phone = is_array($contact) ? ($contact['phone'] ?? null) : null;
        $email = is_array($contact) ? ($contact['email'] ?? null) : null;

        if (empty($name) || empty($url)) {
            return [];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'AccountingService',
            'name' => $name,
            'url' => $url,
        ];

        if ($logo) {
            $schema['logo'] = $logo;
        }

        if ($phone) {
            $schema['telephone'] = $phone;
        }

        if ($email) {
            $schema['email'] = $email;
        }

        /** @var array<int,array<string,mixed>>|null $offices */
        $offices = SiteSetting::get('organization_addresses');
        if (is_array($offices) && $offices !== []) {
            $schema['address'] = array_map(
                static fn (array $o): array => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $o['street'] ?? '',
                    'addressLocality' => $o['city'] ?? '',
                    'addressCountry' => $o['country'] ?? 'ES',
                ],
                $offices
            );
        }

        return $schema;
    }

    public function getLocalBusinessSchema(): array
    {
        /** @var array<string,mixed>|null $contact */
        $contact = SiteSetting::get('contact');

        $name = SiteSetting::get('site_name', config('app.name', 'AGC Assessors'));
        $url = config('app.url', 'https://agcassessors.com');
        $logo = $this->getLogoUrl();
        $phone = is_array($contact) ? ($contact['phone'] ?? null) : null;
        $email = is_array($contact) ? ($contact['email'] ?? null) : null;

        if (empty($name) || empty($url)) {
            return [];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'AccountingService',
            'name' => $name,
            'url' => $url,
        ];

        if ($logo) {
            $schema['logo'] = $logo;
        }

        if ($phone) {
            $schema['telephone'] = $phone;
        }

        if ($email) {
            $schema['email'] = $email;
        }

        /** @var array<int,array<string,mixed>>|null $offices */
        $offices = SiteSetting::get('organization_addresses');
        if (is_array($offices) && $offices !== []) {
            $schema['address'] = array_map(
                static fn (array $o): array => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $o['street'] ?? '',
                    'addressLocality' => $o['city'] ?? '',
                    'addressCountry' => $o['country'] ?? 'ES',
                ],
                $offices
            );
        }

        return $schema;
    }

    public function getWebsiteSchema(): array
    {
        $url = config('app.url', 'https://agcassessors.com');
        $name = SiteSetting::get('site_name', config('app.name', 'AGC Assessors'));

        if (empty($url)) {
            return [];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'url' => $url,
            'name' => $name,
            'inLanguage' => app()->getLocale(),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => rtrim(route('search'), '/') . '?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    public function getBreadcrumbSchema(array $items): array
    {
        if ($items === []) {
            return [];
        }

        $listItems = [];
        foreach ($items as $position => $item) {
            $listItems[] = [
                '@type' => 'ListItem',
                'position' => $position + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? null,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $listItems,
        ];
    }

    private function getOgImage(): string
    {
        // Primary: SeoSettingsPage stores to seo.global.og_image (PR2)
        $globalOgImage = SiteSetting::get('seo.global.og_image');
        if (is_string($globalOgImage) && $globalOgImage !== '') {
            return $globalOgImage;
        }

        // Legacy fallback: old og_image key (kept for smooth rollout)
        $legacyOgImage = SiteSetting::get('og_image');
        if (is_string($legacyOgImage) && $legacyOgImage !== '') {
            return $legacyOgImage;
        }

        if (file_exists(public_path('images/og-default.jpg'))) {
            return asset('images/og-default.jpg');
        }

        return '';
    }

    private function getLogoUrl(): string
    {
        $configured = SiteSetting::get('logo_url');

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        if (file_exists(public_path('images/logo.png'))) {
            return asset('images/logo.png');
        }

        return '';
    }
}
