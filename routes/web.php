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

// Locale switcher — sets session first so localeSessionRedirect doesn't fight it
Route::get('/switch-locale/{locale}', function (string $locale) {
    $supported = array_keys(LaravelLocalization::getSupportedLocales());
    if (in_array($locale, $supported, true)) {
        session()->put('locale', $locale);
        app()->setLocale($locale);
    }
    $previous = url()->previous('/');
    $target = LaravelLocalization::getLocalizedURL($locale, $previous);

    return redirect($target);
})->name('locale.switch');

Route::group(
    ['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']],
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

        Route::get(LaravelLocalization::transRoute('routes.offices'), [OfficesController::class, 'index'])->name('offices.index');

        Route::get(LaravelLocalization::transRoute('routes.careers'), [WorkWithUsController::class, 'index'])->name('careers.index');
        Route::post(LaravelLocalization::transRoute('routes.careers'), [WorkWithUsController::class, 'store'])->name('careers.store')->middleware('spam', 'throttle:3,60');

        Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show');
    }
);
