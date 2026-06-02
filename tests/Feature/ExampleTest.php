<?php

namespace Tests\Feature;

use Database\Seeders\HomeSectionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_localized_home_returns_a_successful_response(): void
    {
        $this->seed(HomeSectionSeeder::class);

        $response = $this->get('/', ['Accept-Language' => 'ca']);

        $response->assertOk();
    }
}
