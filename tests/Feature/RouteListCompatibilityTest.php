<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Tests\TestCase;

final class RouteListCompatibilityTest extends TestCase
{
    public function test_route_listing_remains_available_as_json(): void
    {
        $this->assertSame(0, Artisan::call('route:list', ['--json' => true]));

        $routes = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertContains('/', array_column($routes, 'uri'));
    }

    public function test_translated_route_listing_accepts_the_locale_argument(): void
    {
        $process = new Process([
            PHP_BINARY,
            base_path('artisan'),
            'route:trans:list',
            'es',
            '--json',
        ], base_path());

        $this->assertSame(0, $process->run(), $process->getErrorOutput());

        $routes = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertContains('es', array_column($routes, 'uri'));
    }
}
