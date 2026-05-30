<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use AGC\Domain\News\Repositories\NewsRepository;
use AGC\Domain\Shared\ValueObjects\Slug;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class NewsController extends Controller
{
    private const PER_PAGE = 9;

    public function __construct(
        private readonly NewsRepository $news,
    ) {}

    public function index(Request $request): View
    {
        $page = max(1, (int) $request->query('page', 1));
        $articles = $this->news->findPublished(limit: self::PER_PAGE, offset: ($page - 1) * self::PER_PAGE);
        $total = $this->news->countPublished();

        return view('public.news.index', [
            'news'  => $articles,
            'total' => $total,
            'page'  => $page,
        ]);
    }

    public function show(string $slug): View
    {
        $article = $this->news->findBySlug(new Slug($slug));

        abort_if($article === null, 404);

        return view('public.news.show', ['article' => $article]);
    }
}
