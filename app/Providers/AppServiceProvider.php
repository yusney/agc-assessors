<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\View\Composers\SeoComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private const AGC_MODEL_NAMESPACE = 'AGC\\Infrastructure\\Persistence\\Eloquent\\Models\\';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.public', SeoComposer::class);

        Gate::guessPolicyNamesUsing(static fn (string $modelClass): ?string => static::guessPolicyName($modelClass));
    }

    /**
     * Resolve the policy class name for a given model class.
     *
     * Laravel's auto-resolution fails for models outside App\Models\*, so we
     * manually map AGC infrastructure models to their App\Policies\* counterparts.
     */
    public static function guessPolicyName(string $modelClass): ?string
    {
        if (str_starts_with($modelClass, self::AGC_MODEL_NAMESPACE)) {
            return 'App\\Policies\\'.class_basename($modelClass).'Policy';
        }

        return null;
    }
}
