<?php

declare(strict_types=1);

namespace App\Http\View\Composers;

use AGC\Domain\Offices\Repositories\OfficeRepositoryInterface;
use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use Awcodes\Curator\Models\Media;
use Illuminate\View\View;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

final class SeoComposer
{
    public function __construct(
        private readonly OfficeRepositoryInterface $officeRepository,
    ) {}

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

        // Emit one LocalBusiness schema per active office on the offices page.
        // Wrapped in a @graph so Google treats them as a single, related cluster.
        $routeName = optional($view->getData()['__currentRoute'] ?? null)->getName();
        $currentPath = (string) request()->path();
        $isOfficesPage = $routeName === 'offices.index'
            || str_contains($currentPath, 'oficinas')
            || str_contains($currentPath, 'oficines')
            || str_contains($currentPath, 'offices');

        if ($isOfficesPage) {
            foreach ($this->getOfficesLocalBusinessSchemas() as $officeSchema) {
                $schemas[] = $officeSchema;
            }
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
        // Primary: SeoSettingsPage stores to seo.global.og_image_media_id (PR2)
        $mediaId = SiteSetting::get('seo.global.og_image_media_id');
        if (is_int($mediaId) && $mediaId > 0) {
            $media = Media::find($mediaId);
            if ($media !== null) {
                return $media->url;
            }
        }

        // Legacy fallback: old og_image URL (kept for smooth rollout)
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

    /**
     * Build one LocalBusiness schema per active office.
     * Used on the offices index page so Google can index each location
     * as a distinct local entity even though they share a single URL.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getOfficesLocalBusinessSchemas(): array
    {
        $offices = $this->officeRepository->findAllActive();
        if ($offices === []) {
            return [];
        }

        $siteUrl = rtrim((string) config('app.url', 'https://agcassessors.com'), '/');
        $baseName = (string) SiteSetting::get('site_name', config('app.name', 'AGC Assessors'));
        $locale = (string) app()->getLocale();

        $items = [];
        foreach ($offices as $office) {
            $city = $office->city()->get($locale) !== ''
                ? $office->city()->get($locale)
                : ($office->city()->get('ca') ?? '');

            $address = $office->address()->get($locale) !== ''
                ? $office->address()->get($locale)
                : ($office->address()->get('ca') ?? '');

            if ($city === '' || $address === '') {
                continue;
            }

            $description = $office->description()->get($locale) !== ''
                ? $office->description()->get($locale)
                : ($office->description()->get('ca') ?? null);

            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'LocalBusiness',
                '@id' => $siteUrl . '/oficinas#office-' . $office->id(),
                'name' => $baseName . ' - ' . $city,
                'url' => $siteUrl . '/oficinas#office-' . $office->id(),
                'description' => $description,
                'telephone' => $office->phone(),
                'email' => $office->email(),
                'image' => $office->coverUrl(),
                'priceRange' => '€€',
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $address,
                    'addressLocality' => $city,
                    'addressRegion' => 'Barcelona',
                    'addressCountry' => 'ES',
                ],
                'parentOrganization' => [
                    '@type' => 'AccountingService',
                    'name' => $baseName,
                    'url' => $siteUrl,
                ],
            ];

            $schema = array_filter($schema, static fn ($v) => $v !== null && $v !== '');

            if ($office->lat() !== null && $office->lng() !== null) {
                $schema['geo'] = [
                    '@type' => 'GeoCoordinates',
                    'latitude' => (float) $office->lat(),
                    'longitude' => (float) $office->lng(),
                ];
            }

            $openingHoursValue = $office->openingHours()?->get($locale) !== ''
                ? $office->openingHours()?->get($locale)
                : ($office->openingHours()?->get('ca') ?? null);

            if (is_string($openingHoursValue) && $openingHoursValue !== '') {
                $specs = $this->buildOpeningHoursSpec($openingHoursValue);
                if ($specs !== []) {
                    $schema['openingHoursSpecification'] = $specs;
                }
            }

            $serviceAreaList = $office->serviceAreaList($locale);
            if ($serviceAreaList !== []) {
                $schema['areaServed'] = array_map(
                    static fn (string $area): array => [
                        '@type' => 'City',
                        'name' => $area,
                    ],
                    $serviceAreaList
                );
            }

            $items[] = $schema;
        }

        return $items;
    }

    /**
     * Parse a free-text opening hours string into schema.org OpeningHoursSpecification.
     * Accepts lines like:
     *   "Lunes a jueves: 9:00-18:00"
     *   "Friday: 9:00-14:00"
     *   "Lunes: cerrado"
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildOpeningHoursSpec(string $raw): array
    {
        $dayMap = [
            'ca' => [
                'dilluns' => 'Monday', 'dimarts' => 'Tuesday', 'dimecres' => 'Wednesday',
                'dijous' => 'Thursday', 'divendres' => 'Friday',
                'dissabte' => 'Saturday', 'diumenge' => 'Sunday',
            ],
            'es' => [
                'lunes' => 'Monday', 'martes' => 'Tuesday', 'miercoles' => 'Wednesday', 'miércoles' => 'Wednesday',
                'jueves' => 'Thursday', 'viernes' => 'Friday',
                'sabado' => 'Saturday', 'sábado' => 'Saturday', 'domingo' => 'Sunday',
            ],
            'en' => [
                'monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday',
                'thursday' => 'Thursday', 'friday' => 'Friday',
                'saturday' => 'Saturday', 'sunday' => 'Sunday',
            ],
        ];

        $locale = (string) app()->getLocale();
        $map = $dayMap[$locale] ?? $dayMap['es'];
        $englishDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $specs = [];
        $lines = preg_split('/[\n;]+/', $raw) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $lower = mb_strtolower($line);
            if (str_contains($lower, 'tancat') || str_contains($lower, 'cerrado') || str_contains($lower, 'closed')) {
                continue;
            }

            if (!preg_match('/^(.+?):\s*(\d{1,2}:\d{2})\s*[–\-—to]+\s*(\d{1,2}:\d{2})/iu', $line, $m)) {
                continue;
            }

            $dayPart = mb_strtolower(trim($m[1]));
            $opens = $m[2];
            $closes = $m[3];

            $matchedDays = [];

            // Try to match "<day1> a <day2>" / "<day1> to <day2>" range
            $rangeStart = null;
            $rangeEnd = null;
            foreach ($map as $localName => $enName) {
                if (str_contains($dayPart, $localName)) {
                    if ($rangeStart === null) {
                        $rangeStart = $enName;
                    }
                    $rangeEnd = $enName;
                }
            }

            if ($rangeStart !== null && $rangeEnd !== null) {
                $startIdx = array_search($rangeStart, $englishDays, true);
                $endIdx = array_search($rangeEnd, $englishDays, true);
                if ($startIdx !== false && $endIdx !== false && $endIdx >= $startIdx) {
                    $matchedDays = array_slice($englishDays, $startIdx, $endIdx - $startIdx + 1);
                }
            }

            if ($matchedDays === [] && $rangeStart !== null) {
                $matchedDays = [$rangeStart];
            }

            foreach ($matchedDays as $day) {
                $specs[] = [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => $day,
                    'opens' => $opens,
                    'closes' => $closes,
                ];
            }
        }

        return $specs;
    }
}
