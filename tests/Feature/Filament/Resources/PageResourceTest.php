<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use AGC\Filament\Resources\PageResource;
use Awcodes\Curator\Components\Forms\RichEditor\AttachCuratorMediaPlugin;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Task 2.2.1 — RED: Test that AttachCuratorMediaPlugin is registered on content fields.
 *
 * Spec refs: R3.1, R5.1
 *
 * Uses Schema::make(null) + Reflection to inspect the form schema without
 * triggering Livewire evaluation (getFlatComponents calls getLivewire() internally).
 */
final class PageResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Find a RichEditor field by name in the schema using Reflection traversal.
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
     * @return array<Component|Action>
     */
    private function readRawChildren(object $obj): array
    {
        $ref = new \ReflectionObject($obj);
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

    private function searchComponentForField(object $component, string $name): ?Field
    {
        foreach ($this->readRawChildren($component) as $child) {
            if ($child instanceof Field && $child->getName() === $name) {
                return $child;
            }

            if ($child instanceof Component) {
                $found = $this->searchComponentForField($child, $name);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    #[Test]
    #[DataProvider('contentFieldNamesProvider')]
    public function test_page_content_fields_have_curator_plugin(string $fieldName): void
    {
        $schema = PageResource::form(Schema::make());
        $field = $this->findFieldInSchema($schema, $fieldName);

        $this->assertNotNull($field, "Field '{$fieldName}' must exist in PageResource form");

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

    #[Test]
    #[DataProvider('contentFieldNamesProvider')]
    public function test_page_content_fields_have_attach_curator_media_in_toolbar(string $fieldName): void
    {
        $schema = PageResource::form(Schema::make());
        $field = $this->findFieldInSchema($schema, $fieldName);

        $this->assertNotNull($field, "Field '{$fieldName}' must exist in PageResource form");

        $refl = new \ReflectionClass($field);
        $prop = $refl->getProperty('toolbarButtons');
        $prop->setAccessible(true);
        $toolbarButtons = $prop->getValue($field);

        $flat = [];
        foreach ($toolbarButtons as $group) {
            if (is_array($group)) {
                foreach ($group as $item) {
                    if (is_string($item)) {
                        $flat[] = $item;
                    }
                }
            } elseif (is_string($group)) {
                $flat[] = $group;
            }
        }

        $this->assertContains(
            'attachCuratorMedia',
            $flat,
            "Field '{$fieldName}' toolbar must include 'attachCuratorMedia'"
        );
    }

    /** @return array<string, array{0: string}> */
    public static function contentFieldNamesProvider(): array
    {
        return [
            'content.ca (Català)' => ['content.ca'],
            'content.es (Español)' => ['content.es'],
            'content.en (English)' => ['content.en'],
        ];
    }
}
