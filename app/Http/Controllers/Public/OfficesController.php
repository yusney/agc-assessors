<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use AGC\Domain\Offices\Repositories\OfficeRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

final class OfficesController extends Controller
{
    public function __construct(
        private readonly OfficeRepositoryInterface $offices,
    ) {}

    public function index(): View
    {
        $offices = $this->offices->findAllActive();

        $officesGeoJson = array_values(
            array_filter(
                array_map(function ($office) {
                    if ($office->lat() === null || $office->lng() === null) {
                        return null;
                    }

                    return [
                        'name' => $office->name()->get(app()->getLocale()),
                        'address' => $office->address()->get(app()->getLocale()),
                        'lat' => $office->lat(),
                        'lng' => $office->lng(),
                    ];
                }, $offices),
            )
        );

        return view('public.pages.offices.index', [
            'offices' => $offices,
            'officesGeoJson' => $officesGeoJson,
        ]);
    }

    public function show(string $slug): View
    {
        $locale = (string) app()->getLocale();
        $office = $this->offices->findActiveBySlug($slug, $locale);

        abort_if($office === null, 404);

        $officeGeoJson = ($office->lat() !== null && $office->lng() !== null)
            ? [[
                'name' => $office->name()->get($locale),
                'address' => $office->address()->get($locale),
                'lat' => $office->lat(),
                'lng' => $office->lng(),
            ]]
            : [];

        $breadcrumbs = [
            ['name' => __('messages.nav.home'), 'url' => LaravelLocalization::getLocalizedURL($locale, '/')],
            ['name' => __('messages.offices.title'), 'url' => LaravelLocalization::getLocalizedURL($locale, '/oficines')],
            ['name' => $office->city()->get($locale), 'url' => null],
        ];

        return view('public.pages.offices.show', [
            'office' => $office,
            'officeGeoJson' => $officeGeoJson,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
