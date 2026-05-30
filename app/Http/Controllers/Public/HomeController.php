<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use AGC\Domain\News\Repositories\NewsRepository;
use AGC\Domain\Offices\Repositories\OfficeRepositoryInterface;
use AGC\Domain\Service\Repositories\ServiceRepository;
use AGC\Infrastructure\Persistence\Eloquent\Models\HomeSection;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class HomeController extends Controller
{
    public function __construct(
        private readonly ServiceRepository $services,
        private readonly NewsRepository $news,
        private readonly OfficeRepositoryInterface $officeRepository,
    ) {}

    public function __invoke(): View
    {
        $sections = HomeSection::query()->active()->ordered()->get();
        $newsLimit = (int) $sections
            ->where('type', 'news_highlight')
            ->max(fn (HomeSection $section): int => (int) $section->setting('limit', 3));

        return view('public.pages.home', [
            'sections'   => $sections,
            'services'   => $this->services->findAllActive(),
            'news'       => $this->news->findPublished(limit: max(3, $newsLimit)),
            'offices'    => $this->officeRepository->findAllActive(),
            'mapsApiKey' => config('services.google_maps.key', ''),
        ]);
    }
}
