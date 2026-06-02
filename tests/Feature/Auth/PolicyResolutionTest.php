<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Providers\AppServiceProvider;
use Tests\TestCase;

/**
 * Verify that AppServiceProvider::guessPolicyName() resolves AGC infrastructure
 * models to the correct policy classes in App\Policies.
 *
 * These are pure unit tests — no database required.
 */
final class PolicyResolutionTest extends TestCase
{
    public function test_news_model_resolves_to_news_model_policy(): void
    {
        $policy = AppServiceProvider::guessPolicyName(
            'AGC\\Infrastructure\\Persistence\\Eloquent\\Models\\NewsModel'
        );

        $this->assertSame('App\\Policies\\NewsModelPolicy', $policy);
    }

    public function test_eloquent_office_resolves_to_eloquent_office_policy(): void
    {
        $policy = AppServiceProvider::guessPolicyName(
            'AGC\\Infrastructure\\Persistence\\Eloquent\\Models\\EloquentOffice'
        );

        $this->assertSame('App\\Policies\\EloquentOfficePolicy', $policy);
    }

    public function test_home_section_resolves_to_home_section_policy(): void
    {
        $policy = AppServiceProvider::guessPolicyName(
            'AGC\\Infrastructure\\Persistence\\Eloquent\\Models\\HomeSection'
        );

        $this->assertSame('App\\Policies\\HomeSectionPolicy', $policy);
    }

    public function test_menu_item_resolves_to_menu_item_policy(): void
    {
        $policy = AppServiceProvider::guessPolicyName(
            'AGC\\Infrastructure\\Persistence\\Eloquent\\Models\\MenuItem'
        );

        $this->assertSame('App\\Policies\\MenuItemPolicy', $policy);
    }

    public function test_non_agc_model_returns_null(): void
    {
        $policy = AppServiceProvider::guessPolicyName('App\\Models\\User');

        $this->assertNull($policy);
    }

    public function test_arbitrary_class_outside_agc_namespace_returns_null(): void
    {
        $policy = AppServiceProvider::guessPolicyName('Spatie\\Permission\\Models\\Role');

        $this->assertNull($policy);
    }
}
