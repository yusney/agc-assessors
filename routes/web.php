<?php

use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\NewsletterController;
use App\Http\Controllers\Public\OfficesController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\ServicesController;
use App\Http\Controllers\Public\TeamController;
use App\Http\Controllers\Public\WorkWithUsController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

// Redirect root to localized default
Route::get('/', function () {
    return redirect(LaravelLocalization::getLocalizedURL(app()->getLocale(), '/'));
});

// Locale switcher — preserves the current page across the locale change.
// Uses the Referer header (set when the user clicks from a real page) and
// strips the locale prefix so LaravelLocalization can rebuild the URL for
// the target locale without appending "?locale=".
Route::get('/switch-locale/{locale}', function (string $locale) {
    $supported = array_keys(LaravelLocalization::getSupportedLocales());
    if (in_array($locale, $supported, true)) {
        session()->put('locale', $locale);
        app()->setLocale($locale);
    }

    // Prefer the Referer (the page the user was on) over url()->previous(),
    // which would return this /switch-locale URL itself.
    $referer = request()->headers->get('referer');
    if (! is_string($referer) || $referer === '') {
        $referer = url()->previous('/');
    }

    // Extract just the path so LaravelLocalization can rebuild it for the
    // target locale (it ignores paths like /switch-locale/{locale} and
    // would otherwise append a query string).
    $path = parse_url($referer, PHP_URL_PATH) ?: '/';
    $query = parse_url($referer, PHP_URL_QUERY);
    // Strip the current locale prefix so we don't double-prefix the URL.
    $locales = $supported;
    foreach ($locales as $loc) {
        $prefix = '/' . $loc;
        if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
            $path = substr($path, strlen($prefix));
            if ($path === '') {
                $path = '/';
            }
            break;
        }
    }
    // Drop any existing locale query param to avoid "?locale=" being added.
    $query = $query ? preg_replace('/(?:^|&)locale=[^&]*/', '', $query) : null;
    $query = $query ? ltrim($query, '&') : null;

    $target = LaravelLocalization::getLocalizedURL($locale, $path);
    if ($query) {
        $separator = str_contains($target, '?') ? '&' : '?';
        $target .= $separator . $query;
    }

    return redirect($target);
})->name('locale.switch');

// Sitemap — must be outside the locale group so /sitemap.xml is accessible without locale prefix
Route::get('/sitemap.xml', [\App\Http\Controllers\Public\SitemapController::class, 'index'])
    ->name('sitemap');

// We register public routes for each supported locale explicitly because the
// package's `setLocale()` + `transRoute()` combo only registers one variant of
// each route (the one for the active locale at boot). Registering all three
// ensures /es/... and /en/... resolve correctly, which is what the locale
// switcher redirects to and what hreflang alternates point to.
$supportedLocales = array_keys(LaravelLocalization::getSupportedLocales());
$hideDefaultLocaleInURL = (bool) config('laravellocalization.hideDefaultLocaleInURL', false);
$defaultLocale = (string) config('app.locale');

foreach ($supportedLocales as $localeCode) {
    $prefix = ($hideDefaultLocaleInURL && $localeCode === $defaultLocale) ? '' : $localeCode;

    Route::group(
        [
            'prefix' => $prefix,
            'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
        ],
        function () {
            Route::get('/', HomeController::class)->name('home');

            Route::get('/search', SearchController::class)->name('search');

            Route::get('/actualitat', [NewsController::class, 'index'])->name('news.index');
            Route::get('/actualitat/{slug}', [NewsController::class, 'show'])->name('news.show');

            Route::get('/serveis', [ServicesController::class, 'index'])->name('services.index');
            Route::get('/serveis/{slug}', [ServicesController::class, 'show'])->name('services.show');

            Route::get('/equip', TeamController::class)->name('team');

            Route::get('/contacte', [ContactController::class, 'index'])->name('contact');
            Route::post('/contacte', [ContactController::class, 'store'])->name('contact.store')->middleware('spam', 'throttle:10,1');

            Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store')->middleware('spam', 'throttle:5,1');
            Route::get('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribeForm'])->name('newsletter.unsubscribe.form');
            Route::post('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribeByForm'])->name('newsletter.unsubscribe.process')->middleware('spam', 'throttle:5,1');
            Route::get('/unsubscribe/{email}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

            Route::get('/oficines', [OfficesController::class, 'index'])->name('offices.index');
            Route::get('/oficines/{slug}', [OfficesController::class, 'show'])->name('offices.show');

            Route::get('/treballa-amb-nosaltres', [WorkWithUsController::class, 'index'])->name('careers.index');
            Route::post('/treballa-amb-nosaltres', [WorkWithUsController::class, 'store'])->name('careers.store')->middleware('spam', 'throttle:3,60');

            Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show');
        }
    );
}
