<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class SearchController extends Controller
{
    private const PER_PAGE = 10;

    public function __invoke(Request $request): \Illuminate\View\View
    {
        $query     = trim($request->get('q', ''));
        $locale    = app()->getLocale();
        $vectorCol = 'search_vector_' . $locale;
        $langConf  = match ($locale) {
            'ca'    => 'catalan',
            'es'    => 'spanish',
            default => 'english',
        };

        $results   = new Collection();
        $paginator = null;

        if (mb_strlen($query) >= 3) {
            $page   = max(1, (int) $request->get('page', 1));
            $offset = ($page - 1) * self::PER_PAGE;

            $unionSql = "
                SELECT
                    'news' AS source_type,
                    id,
                    (title->>'{$locale}') AS title,
                    ts_headline(
                        '{$langConf}',
                        coalesce(title->>'{$locale}','') || ' ' || coalesce(excerpt->>'{$locale}','') || ' ' || coalesce(body->>'{$locale}',''),
                        plainto_tsquery('{$langConf}', ?),
                        'MaxWords=35,MinWords=15,StartSel=<mark>,StopSel=</mark>,HighlightAll=false'
                    ) AS snippet,
                    slug,
                    ts_rank({$vectorCol}, plainto_tsquery('{$langConf}', ?)) AS rank
                FROM news_articles
                WHERE published = true
                  AND deleted_at IS NULL
                  AND {$vectorCol} @@ plainto_tsquery('{$langConf}', ?)

                UNION ALL

                SELECT
                    'service' AS source_type,
                    id,
                    (name->>'{$locale}') AS title,
                    ts_headline(
                        '{$langConf}',
                        coalesce(name->>'{$locale}','') || ' ' || coalesce(description->>'{$locale}',''),
                        plainto_tsquery('{$langConf}', ?),
                        'MaxWords=35,MinWords=15,StartSel=<mark>,StopSel=</mark>,HighlightAll=false'
                    ) AS snippet,
                    slug,
                    ts_rank({$vectorCol}, plainto_tsquery('{$langConf}', ?)) AS rank
                FROM services
                WHERE active = true
                  AND {$vectorCol} @@ plainto_tsquery('{$langConf}', ?)

                UNION ALL

                SELECT
                    'page' AS source_type,
                    id,
                    (title->>'{$locale}') AS title,
                    ts_headline(
                        '{$langConf}',
                        coalesce(title->>'{$locale}','') || ' ' || coalesce(content->>'{$locale}',''),
                        plainto_tsquery('{$langConf}', ?),
                        'MaxWords=35,MinWords=15,StartSel=<mark>,StopSel=</mark>,HighlightAll=false'
                    ) AS snippet,
                    slug,
                    ts_rank({$vectorCol}, plainto_tsquery('{$langConf}', ?)) AS rank
                FROM pages
                WHERE published = true
                  AND deleted_at IS NULL
                  AND {$vectorCol} @@ plainto_tsquery('{$langConf}', ?)
            ";

            // Each source needs 3 bindings: ts_headline query, ts_rank query, WHERE @@ query
            $bindings = [
                $query, $query, $query, // news
                $query, $query, $query, // service
                $query, $query, $query, // page
            ];

            $total = DB::selectOne(
                "SELECT COUNT(*) AS total FROM ({$unionSql}) AS combined",
                $bindings
            )?->total ?? 0;

            $rows = DB::select(
                "{$unionSql} ORDER BY rank DESC LIMIT ? OFFSET ?",
                array_merge($bindings, [self::PER_PAGE, $offset])
            );

            $results   = new Collection($rows);
            $paginator = new LengthAwarePaginator(
                $results,
                (int) $total,
                self::PER_PAGE,
                $page,
                ['path' => $request->url(), 'query' => ['q' => $query]]
            );
        }

        return view('public.search.index', [
            'results'   => $results,
            'query'     => $query,
            'paginator' => $paginator,
        ]);
    }
}
