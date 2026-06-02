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
    }

    private function getCanonicalUrl(): string
    {
        return LaravelLocalization::getLocalizedURL(
            app()->getLocale(),
            url()->current(),
            [],
            true
        );
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
                    'urlTemplate' => $url.'/'.app()->getLocale().'?q={search_term_string}',
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
        $configured = SiteSetting::get('og_image');

        if (is_string($configured) && $configured !== '') {
            return $configured;
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
