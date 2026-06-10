<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets the application locale from the URL's first path segment.
 *
 * The bundled LocaleSessionRedirect middleware from mcamara/laravel-localization
 * saves the locale in the session but does NOT call app()->setLocale() when the
 * URL already has the prefix. This causes the navbar language switcher to keep
 * showing the default locale (ca) as active on /es/... and /en/... pages
 * because app()->getLocale() always returns the default.
 *
 * This middleware runs BEFORE the package's LocaleSessionRedirect so that by
 * the time the session is read, the app locale is already aligned with the URL.
 */
final class SetLocaleFromUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(LaravelLocalization::getSupportedLocales());
        $path = $request->path();
        $segments = $path === '' ? [] : explode('/', $path);
        $first = $segments[0] ?? '';

        if (in_array($first, $supported, true)) {
            app()->setLocale($first);
        }

        return $next($request);
    }
}
