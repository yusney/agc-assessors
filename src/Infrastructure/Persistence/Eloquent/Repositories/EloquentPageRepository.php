<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Repositories;

use AGC\Domain\Page\Entities\Page;
use AGC\Domain\Page\Repositories\PageRepository;
use AGC\Domain\Shared\ValueObjects\SEOData;
use AGC\Domain\Shared\ValueObjects\Slug;
use AGC\Domain\Shared\ValueObjects\TranslatableString;
use AGC\Infrastructure\Persistence\Eloquent\Models\PageModel;

final class EloquentPageRepository implements PageRepository
{
    public function findById(int $id): ?Page
    {
        $model = PageModel::find($id);
        return $model ? $this->toDomain($model) : null;
    }

    public function findBySlug(Slug $slug): ?Page
    {
        $model = PageModel::where('slug', $slug->value())->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function findAllPublished(): array
    {
        return PageModel::where('published', true)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn ($m) => $this->toDomain($m))
            ->all();
    }

    public function save(Page $page): void
    {
        $data = [
            'slug'            => $page->slug()->value(),
            'title'           => $page->title()->toArray(),
            'content'         => $page->content()->toArray(),
            'seo_title'       => $page->seo()->title()->toArray(),
            'seo_description' => $page->seo()->description()->toArray(),
            'seo_canonical'   => $page->seo()->canonicalUrl(),
            'published'       => $page->isPublished(),
        ];

        if ($page->id()) {
            PageModel::where('id', $page->id())->update($data);
        } else {
            PageModel::create($data);
        }
    }

    public function delete(int $id): void
    {
        PageModel::destroy($id);
    }

    private function toDomain(PageModel $model): Page
    {
        return new Page(
            id: $model->id,
            title: new TranslatableString($model->getTranslations('title')),
            content: new TranslatableString($model->getTranslations('content')),
            slug: new Slug($model->slug),
            seo: new SEOData(
                title: new TranslatableString($model->getTranslations('seo_title') ?: []),
                description: new TranslatableString($model->getTranslations('seo_description') ?: []),
                canonicalUrl: $model->seo_canonical,
            ),
            published: (bool) $model->published,
            createdAt: $model->created_at ? \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()) : null,
            updatedAt: $model->updated_at ? \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()) : null,
        );
    }
}
