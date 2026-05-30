<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use AGC\Domain\Offices\Repositories\OfficeRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

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
                        'name'    => $office->name()->get(app()->getLocale()),
                        'address' => $office->address()->get(app()->getLocale()),
                        'lat'     => $office->lat(),
                        'lng'     => $office->lng(),
                    ];
                }, $offices),
            )
        );

        return view('public.pages.offices.index', [
            'offices'        => $offices,
            'officesGeoJson' => $officesGeoJson,
        ]);
    }
}
