<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Repositories;

use AGC\Domain\News\Entities\NewsArticle;
use AGC\Domain\News\Repositories\NewsRepository;
use AGC\Domain\Shared\ValueObjects\SEOData;
use AGC\Domain\Shared\ValueObjects\Slug;
use AGC\Domain\Shared\ValueObjects\TranslatableString;
use AGC\Infrastructure\Persistence\Eloquent\Models\NewsModel;
use Awcodes\Curator\Models\Media;

final class EloquentNewsRepository implements NewsRepository
{
    public function findById(int $id): ?NewsArticle
    {
        $model = NewsModel::find($id);
        return $model ? $this->toDomain($model) : null;
    }

    public function findBySlug(Slug $slug): ?NewsArticle
    {
        $model = NewsModel::where('slug', $slug->value())->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function findPublished(int $limit = 10, int $offset = 0): array
    {
        return NewsModel::where('published', true)
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(fn ($m) => $this->toDomain($m))
            ->all();
    }    public function countPublished(): int
    {
        return NewsModel::where('published', true)->count();
    }

    public function save(NewsArticle $article): void
    {
        $data = [
            'slug'            => $article->slug()->value(),
            'title'           => $article->title()->toArray(),
            'excerpt'         => $article->excerpt()->toArray(),
            'body'            => $article->body()->toArray(),
            'seo_title'       => $article->seo()->title()->toArray(),
            'seo_description' => $article->seo()->description()->toArray(),
            'seo_canonical'   => $article->seo()->canonicalUrl(),
            'published'       => $article->isPublished(),
            'published_at'    => $article->publishedAt()?->format('Y-m-d H:i:s'),
        ];

        if ($article->id()) {
            NewsModel::where('id', $article->id())->update($data);
        } else {
            NewsModel::create($data);
        }
    }

    public function delete(int $id): void
    {
        NewsModel::destroy($id);
    }

    private function toDomain(NewsModel $model): NewsArticle
    {
        return new NewsArticle(
            id: $model->id,
            title: new TranslatableString($model->getTranslations('title')),
            excerpt: new TranslatableString($model->getTranslations('excerpt')),
            body: new TranslatableString($model->getTranslations('body')),
            slug: new Slug($model->slug),
            seo: new SEOData(
                title: new TranslatableString($model->getTranslations('seo_title') ?: []),
                description: new TranslatableString($model->getTranslations('seo_description') ?: []),
                canonicalUrl: $model->seo_canonical,
            ),
            published: (bool) $model->published,
            publishedAt: $model->published_at
                ? \DateTimeImmutable::createFromMutable($model->published_at->toDateTime())
                : null,
            coverUrl: $model->cover_media_id
                ? Media::find($model->cover_media_id)?->url
                : null,
        );
    }
}
