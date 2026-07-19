<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use AGC\Filament\Resources\HomeSectionResource;
use AGC\Infrastructure\Persistence\Eloquent\Models\HomeSection;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Tests\TestCase;

final class HomeSectionResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_intro_form_contains_primary_cta_fields(): void
    {
        $schema = HomeSectionResource::form(Schema::make());
        $buttonsSection = $this->findSection($schema, 'Botones');
        $textSection = $this->findSection($schema, 'Textos');
        $ctaUrlField = $this->findFieldInComponent($buttonsSection, 'cta_url');
        $ctaLabelField = $this->findFieldInComponent($textSection, 'cta_label.ca');

        $this->assertNotNull($buttonsSection);
        $this->assertNotNull($textSection);
        $this->assertNotNull($ctaUrlField);
        $this->assertNotNull($ctaLabelField);
        $this->assertNotNull($this->findFieldInComponent($textSection, 'cta_label.es'));
        $this->assertNotNull($this->findFieldInComponent($textSection, 'cta_label.en'));
        $this->assertFalse($this->isHiddenForType($buttonsSection, 'intro'));
        $this->assertFalse($this->isHiddenForType($ctaLabelField, 'intro'));
        $this->assertTrue($this->isHiddenForType($buttonsSection, 'stats'));
        $this->assertTrue($this->isHiddenForType($ctaLabelField, 'stats'));
    }

    public function test_intro_renders_primary_cta_only_when_label_and_url_exist(): void
    {
        $originalLocale = app()->getLocale();

        try {
            app()->setLocale('ca');
            $expectedCtaUrl = LaravelLocalization::getLocalizedURL(app()->getLocale(), '/pages/qui-som', [], false);

            $section = new HomeSection;
            $section->setAttribute('title', ['ca' => 'Qui som']);
            $section->setAttribute('body', ['ca' => 'Coneix el nostre equip.']);
            $section->setAttribute('cta_label', ['ca' => 'Sobre nosaltres']);
            $section->setAttribute('cta_url', '/pages/qui-som');

            $view = $this->view('public.home-sections.intro', ['section' => $section]);

            $view->assertSee('Sobre nosaltres');
            $view->assertSee('href="'.$expectedCtaUrl.'"', false);
            $view->assertSee('aria-hidden="true"', false);

            foreach ([
                'https://example.com/assessors' => 'https://example.com/assessors',
                '//example.com/assessors' => '//example.com/assessors',
            ] as $ctaUrl => $expectedHref) {
                $section->setAttribute('cta_url', $ctaUrl);

                $this->view('public.home-sections.intro', ['section' => $section])
                    ->assertSee('Sobre nosaltres')
                    ->assertSee('href="'.$expectedHref.'"', false);
            }

            $section->setAttribute('cta_label', ['ca' => " \t\n "]);
            $section->setAttribute('cta_url', '/pages/qui-som');

            $this->view('public.home-sections.intro', ['section' => $section])
                ->assertDontSee('href=', false);

            $section->setAttribute('cta_label', ['ca' => 'Sobre nosaltres']);
            $section->setAttribute('cta_url', 'javascript:alert(1)');

            $this->view('public.home-sections.intro', ['section' => $section])
                ->assertDontSee('Sobre nosaltres')
                ->assertDontSee('href=', false);
        } finally {
            app()->setLocale($originalLocale);
        }
    }

    private function findSection(Schema $schema, string $heading): ?Section
    {
        foreach ($this->rawChildren($schema) as $component) {
            if (! $component instanceof Component) {
                continue;
            }

            $section = $this->findSectionInComponent($component, $heading);

            if ($section !== null) {
                return $section;
            }
        }

        return null;
    }

    private function findSectionInComponent(object $component, string $heading): ?Section
    {
        if ($component instanceof Section && $component->getHeading() === $heading) {
            return $component;
        }

        foreach ($this->rawChildren($component) as $child) {
            if ($child instanceof Component) {
                $section = $this->findSectionInComponent($child, $heading);

                if ($section !== null) {
                    return $section;
                }
            }
        }

        return null;
    }

    private function findFieldInComponent(object $component, string $name): ?Field
    {
        foreach ($this->rawChildren($component) as $child) {
            if ($child instanceof Field && $child->getName() === $name) {
                return $child;
            }

            if ($child instanceof Component) {
                $field = $this->findFieldInComponent($child, $name);

                if ($field !== null) {
                    return $field;
                }
            }
        }

        return null;
    }

    private function isHiddenForType(object $component, string $type): bool
    {
        $reflection = new \ReflectionObject($component);
        $property = $reflection->getProperty('isHidden');
        $property->setAccessible(true);
        $hidden = $property->getValue($component);

        if (! $hidden instanceof \Closure) {
            return (bool) $hidden;
        }

        $get = new class($type) extends Get
        {
            public function __construct(private readonly string $type) {}

            public function __invoke(Component|string $path = '', bool $isAbsolute = false): mixed
            {
                return $path === 'type' ? $this->type : null;
            }
        };

        return (bool) $hidden($get);
    }

    /** @return array<object> */
    private function rawChildren(object $component): array
    {
        $reflection = new \ReflectionObject($component);

        foreach ($reflection->getProperties() as $property) {
            if ($property->getName() === 'components') {
                $property->setAccessible(true);
                $children = $property->getValue($component);

                return is_array($children) ? $children : [];
            }

            if ($property->getName() === 'childComponents') {
                $property->setAccessible(true);
                $children = $property->getValue($component);

                return is_array($children['default'] ?? null) ? $children['default'] : [];
            }
        }

        return [];
    }
}
