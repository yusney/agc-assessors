<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use AGC\Infrastructure\Persistence\Eloquent\Models\PageModel;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = PageModel::where('slug', $slug)
            ->where('published', true)
            ->first();

        abort_if($page === null, 404);

        return view('public.pages.show', ['page' => $page]);
    }
}
