<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\View\Composers\SeoComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

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

        // Super-admin bypass: any user bearing the 'super_admin' role is granted
        // every authorization check unconditionally before policies are evaluated.
        //
        // Exception: the super_admin role itself is a protected system role that
        // cannot be deleted — not even by a super_admin user. This prevents
        // accidental or malicious removal of the only full-access role.
        Gate::before(function ($user, string $ability, mixed $arguments = null): ?bool {
            if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
                // Block deletion of the protected super_admin role.
                if (
                    $ability === 'delete'
                    && is_array($arguments)
                    && isset($arguments[0])
                    && $arguments[0] instanceof Role
                    && $arguments[0]->name === config('filament-shield.super_admin.name', 'super_admin')
                ) {
                    return false;
                }

                return true;
            }

            return null; // Delegate to policies / permissions for all other users.
        });

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
