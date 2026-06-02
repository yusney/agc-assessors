<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Providers\AppServiceProvider;
use PHPUnit\Framework\TestCase;

final class AppServiceProviderTest extends TestCase
{
    public function test_agc_model_class_resolves_to_correct_policy_name(): void
    {
        $policy = AppServiceProvider::guessPolicyName(
            'AGC\\Infrastructure\\Persistence\\Eloquent\\Models\\NewsModel',
        );

        $this->assertSame('App\\Policies\\NewsModelPolicy', $policy);
    }

    public function test_agc_team_member_model_resolves_to_correct_policy_name(): void
    {
        $policy = AppServiceProvider::guessPolicyName(
            'AGC\\Infrastructure\\Persistence\\Eloquent\\Models\\TeamMemberModel',
        );

        $this->assertSame('App\\Policies\\TeamMemberModelPolicy', $policy);
    }

    public function test_non_agc_model_returns_null(): void
    {
        $policy = AppServiceProvider::guessPolicyName('App\\Models\\User');

        $this->assertNull($policy);
    }

    public function test_arbitrary_model_outside_agc_namespace_returns_null(): void
    {
        $policy = AppServiceProvider::guessPolicyName('Illuminate\\Foundation\\Auth\\User');

        $this->assertNull($policy);
    }
}
