<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use AGC\Filament\Resources\ServiceResource;
use Awcodes\Curator\Components\Forms\RichEditor\AttachCuratorMediaPlugin;
use Filament\Forms\Components\Field;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 2.3.1 — RED: Test that AttachCuratorMediaPlugin is registered on description fields.
 *
 * Spec refs: R4.1, R5.1
 *
 * Uses Schema::make(null) + Reflection to inspect the form schema without
 * triggering Livewire evaluation (getFlatComponents calls getLivewire() internally).
 */
final class ServiceResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Find a RichEditor field by name in the schema using Reflection traversal.
     *
     * @return Field|null
     */
    private function findFieldInSchema(Schema $schema, string $name): ?Field
    {
        foreach ($this->readRawChildren($schema) as $component) {
            $found = $this->searchComponentForField($component, $name);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    /**
     * @return array<\Filament\Schemas\Components\Component|\Filament\Actions\Action>
     */
    private function readRawChildren(object $obj): array
    {
        $ref   = new \ReflectionObject($obj);
        $props = $ref->getProperties();

        foreach ($props as $prop) {
            if ($prop->getName() === 'components') {
                $prop->setAccessible(true);
                $val = $prop->getValue($obj);

                return is_array($val) ? $val : [];
            }

            if ($prop->getName() === 'childComponents') {
                $prop->setAccessible(true);
                $val = $prop->getValue($obj);

                return is_array($val['default'] ?? null) ? $val['default'] : [];
            }
        }

        return [];
    }

    /**
     * @return Field|null
     */
    private function searchComponentForField(object $component, string $name): ?Field
    {
        foreach ($this->readRawChildren($component) as $child) {
            if ($child instanceof Field && $child->getName() === $name) {
                return $child;
            }

            if ($child instanceof \Filament\Schemas\Components\Component) {
                $found = $this->searchComponentForField($child, $name);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('descriptionFieldNamesProvider')]
    public function test_service_description_fields_have_curator_plugin(string $fieldName): void
    {
        $schema = ServiceResource::form(Schema::make());
        $field  = $this->findFieldInSchema($schema, $fieldName);

        $this->assertNotNull($field, "Field '{$fieldName}' must exist in ServiceResource form");

        // Use Reflection to access the $plugins property directly, bypassing
        // getPlugins() which calls getContentAttribute() requiring a container.
        $refl = new \ReflectionClass($field);
        $prop = $refl->getProperty('plugins');
        $prop->setAccessible(true);
        $plugins = $prop->getValue($field);

        $this->assertNotEmpty($plugins, "Field '{$fieldName}' must have plugins registered");
        $this->assertInstanceOf(
            AttachCuratorMediaPlugin::class,
            $plugins[0],
            "Field '{$fieldName}' first plugin must be AttachCuratorMediaPlugin"
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('descriptionFieldNamesProvider')]
    public function test_service_description_fields_enable_attach_curator_media_toolbar_button(string $fieldName): void
    {
        $schema = ServiceResource::form(Schema::make());
        $field  = $this->findFieldInSchema($schema, $fieldName);

        $this->assertNotNull($field, "Field '{$fieldName}' must exist in ServiceResource form");
        $this->assertTrue(
            $field->hasToolbarButton('attachCuratorMedia'),
            "Field '{$fieldName}' must enable 'attachCuratorMedia' in the toolbar"
        );
    }

    /** @return array<string, array{0: string}> */
    public static function descriptionFieldNamesProvider(): array
    {
        return [
            'description.ca (Català)' => ['description.ca'],
            'description.es (Español)' => ['description.es'],
            'description.en (English)' => ['description.en'],
        ];
    }
}