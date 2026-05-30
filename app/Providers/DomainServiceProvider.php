<?php

declare(strict_types=1);

namespace App\Providers;

use AGC\Domain\News\Repositories\NewsRepository;
use AGC\Domain\Page\Repositories\PageRepository;
use AGC\Domain\Service\Repositories\ServiceRepository;
use AGC\Domain\Team\Repositories\TeamMemberRepository;
use AGC\Infrastructure\Persistence\Eloquent\Repositories\EloquentNewsRepository;
use AGC\Infrastructure\Persistence\Eloquent\Repositories\EloquentPageRepository;
use AGC\Infrastructure\Persistence\Eloquent\Repositories\EloquentServiceRepository;
use AGC\Infrastructure\Persistence\Eloquent\Repositories\EloquentTeamMemberRepository;
use Illuminate\Support\ServiceProvider;

final class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PageRepository::class, EloquentPageRepository::class);
        $this->app->bind(NewsRepository::class, EloquentNewsRepository::class);
        $this->app->bind(ServiceRepository::class, EloquentServiceRepository::class);
        $this->app->bind(TeamMemberRepository::class, EloquentTeamMemberRepository::class);
    }
}
