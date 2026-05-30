<?php

declare(strict_types=1);

namespace App\Providers;

use AGC\Domain\Offices\Repositories\OfficeRepositoryInterface;
use AGC\Infrastructure\Persistence\Eloquent\Repositories\EloquentOfficeRepository;
use Illuminate\Support\ServiceProvider;

final class OfficesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OfficeRepositoryInterface::class, EloquentOfficeRepository::class);
    }
}
