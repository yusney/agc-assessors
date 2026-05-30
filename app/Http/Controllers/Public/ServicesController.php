<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use AGC\Domain\Service\Repositories\ServiceRepository;
use AGC\Domain\Shared\ValueObjects\Slug;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class ServicesController extends Controller
{
    public function __construct(
        private readonly ServiceRepository $services,
    ) {}

    public function index(): View
    {
        return view('public.services.index', [
            'services' => $this->services->findAllActive(),
        ]);
    }

    public function show(string $slug): View
    {
        $service = $this->services->findBySlug(new Slug($slug));

        abort_if($service === null, 404);

        return view('public.services.show', ['service' => $service]);
    }
}
