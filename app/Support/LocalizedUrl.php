<?php

declare(strict_types=1);

namespace App\Support;

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * Centralized helper for building locale-aware URLs.
 *
 * LaravelLocalization::getLocalizedURL() is convenient but fragile in tests
 * because it derives the base URL from the current request and can append
 * unexpected "?locale=" query strings when route names are shared across
 * locale groups. This helper always builds from config('app.url') as the
 * canonical base and only adds the locale prefix when required.
 */
final class LocalizedUrl
{
    /**
     * Build an absolute URL for a bare path and a given locale.
     *
     * @param  string  $path  Bare path without locale prefix (e.g. "/contacte").
     * @param  string|null  $locale  Target locale, defaults to app locale.
     */
    public static function to(string $path, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $path = '/' . ltrim($path, '/');

        $hideDefault = (bool) config('laravellocalization.hideDefaultLocaleInURL', false);
        $defaultLocale = LaravelLocalization::getDefaultLocale();

        $prefix = ($locale === $defaultLocale && $hideDefault)
            ? ''
            : '/' . $locale;

        $base = rtrim((string) config('app.url'), '/');

        return $base . $prefix . $path;
    }

    /**
     * Build a relative path for a bare path and a given locale.
     *
     * @param  string  $path  Bare path without locale prefix.
     * @param  string|null  $locale  Target locale, defaults to app locale.
     */
    public static function path(string $path, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $path = '/' . ltrim($path, '/');

        $hideDefault = (bool) config('laravellocalization.hideDefaultLocaleInURL', false);
        $defaultLocale = LaravelLocalization::getDefaultLocale();

        $prefix = ($locale === $defaultLocale && $hideDefault)
            ? ''
            : '/' . $locale;

        return $prefix . $path;
    }

    /**
     * Determine the active locale from the current request URL, independent of
     * app()->getLocale() which may be stale at view-render time.
     */
    public static function activeLocaleFromUrl(): string
    {
        $supported = array_keys(LaravelLocalization::getSupportedLocales());
        $first = request()->segments()[0] ?? '';

        return in_array($first, $supported, true)
            ? $first
            : LaravelLocalization::getDefaultLocale();
    }

    /**
     * Return the current request path with any locale prefix removed.
     */
    public static function stripLocalePrefix(): string
    {
        $path = '/' . ltrim((string) request()->path(), '/');
        $supported = array_keys(LaravelLocalization::getSupportedLocales());

        foreach ($supported as $locale) {
            $prefix = '/' . $locale;
            if ($path === $prefix) {
                return '/';
            }
            if (str_starts_with($path, $prefix . '/')) {
                return substr($path, strlen($prefix));
            }
        }

        return $path;
    }
}
