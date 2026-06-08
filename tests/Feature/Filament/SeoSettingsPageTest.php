<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use AGC\Filament\Pages\SeoSettingsPage;
use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use App\Models\User;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * PR2 — Admin Global Defaults.
 *
 * Tests for SeoSettingsPage:
 *   - The form schema contains no field with 'keywords' in its name
 *   - Fields for per-locale title/description exist
 *   - Shared og_image field exists (not per-locale)
 *   - Save persists to SiteSetting keys
 *
 * Uses Schema::make(null) to inspect the form schema without HTTP overhead,
 * falling back to an HTTP smoke test for the accessibility assertion.
 */
final class SeoSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    private function makeAdminUser(): User
    {
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        return User::factory()->withRole('super_admin')->create();
    }

    /**
     * Get all flat field names from SeoSettingsPage's form schema.
     *
     * Uses PHP Reflection to traverse the raw component tree without triggering
     * Filament's evaluation machinery (getFlatComponents calls getLivewire()
     * internally, which requires a real Livewire component instance).
     *
     * Architecture:
     *   - Schema stores children in $components (HasComponents trait)
     *   - Component (Section, Tabs, Tab) stores children in $childComponents['default'] (HasChildComponents trait)
     *   - Field (TextInput, Textarea) exposes getName() with no Livewire dependency
     *
     * @return list<string>
     */
    private function getAllFieldNames(): array
    {
        $page   = new SeoSettingsPage();
        $schema = Schema::make(null);
        $page->form($schema);

        return $this->extractFieldNamesFromRaw($schema);
    }

    /**
     * Recursively extract field names from a schema or component using Reflection.
     *
     * @return list<string>
     */
    private function extractFieldNamesFromRaw(object $container): array
    {
        $names = [];

        foreach ($this->readRawChildren($container) as $component) {
            if ($component instanceof \Filament\Forms\Components\Field) {
                $names[] = $component->getName();
            } elseif ($component instanceof \Filament\Schemas\Components\Component) {
                $names = array_merge($names, $this->extractFieldNamesFromRaw($component));
            }
        }

        return $names;
    }

    /**
     * Read the raw child components from a Schema or Component via Reflection.
     * Bypasses Filament's getComponents()/getChildComponents() which trigger getLivewire().
     *
     * @return array<\Filament\Schemas\Components\Component|\Filament\Actions\Action>
     */
    private function readRawChildren(object $obj): array
    {
        $ref   = new \ReflectionObject($obj);
        $props = $ref->getProperties();

        foreach ($props as $prop) {
            if ($prop->getName() === 'components') {
                // Schema (HasComponents trait): children in $components
                $prop->setAccessible(true);
                $val = $prop->getValue($obj);

                return is_array($val) ? $val : [];
            }

            if ($prop->getName() === 'childComponents') {
                // Component (HasChildComponents trait): children in $childComponents['default']
                $prop->setAccessible(true);
                $val = $prop->getValue($obj);

                return is_array($val['default'] ?? null) ? $val['default'] : [];
            }
        }

        return [];
    }

    // ---------------------------------------------------------------------------
    // Section A — Schema inspection: no keywords field
    // ---------------------------------------------------------------------------

    /**
     * Spec: "No keywords field SHALL be present in this form or anywhere in
     * the admin panel."
     *
     * @test
     */
    public function test_seo_settings_page_form_has_no_keywords_field(): void
    {
        $names = $this->getAllFieldNames();

        foreach ($names as $name) {
            $this->assertStringNotContainsString(
                'keyword',
                strtolower($name),
                "Form field '{$name}' must not contain 'keyword' — keywords are out of scope per spec non-goal"
            );
        }
    }

    /**
     * Triangulate: form has at least some fields (proves getFlatComponents ran
     * and the empty-loop gotcha is avoided).
     *
     * @test
     */
    public function test_seo_settings_page_form_has_multiple_fields(): void
    {
        $names = $this->getAllFieldNames();

        // Must have at least title.ca, title.es, title.en,
        // description.ca, description.es, description.en, og_image = 7 fields
        $this->assertGreaterThanOrEqual(
            7,
            count($names),
            'Form must have at least 7 fields (title×3 + description×3 + og_image)'
        );
    }

    // ---------------------------------------------------------------------------
    // Section A — Schema inspection: per-locale title/description fields
    // ---------------------------------------------------------------------------

    /** @test */
    public function test_seo_settings_page_form_has_title_fields_for_all_locales(): void
    {
        $names = $this->getAllFieldNames();

        $this->assertContains('title.ca', $names, 'Form must have title.ca field');
        $this->assertContains('title.es', $names, 'Form must have title.es field');
        $this->assertContains('title.en', $names, 'Form must have title.en field');
    }

    /** @test */
    public function test_seo_settings_page_form_has_description_fields_for_all_locales(): void
    {
        $names = $this->getAllFieldNames();

        $this->assertContains('description.ca', $names, 'Form must have description.ca field');
        $this->assertContains('description.es', $names, 'Form must have description.es field');
        $this->assertContains('description.en', $names, 'Form must have description.en field');
    }

    // ---------------------------------------------------------------------------
    // Section A — Schema inspection: single shared og_image (NOT per-locale)
    // ---------------------------------------------------------------------------

    /** @test */
    public function test_seo_settings_page_form_has_single_shared_og_image_field(): void
    {
        $names = $this->getAllFieldNames();

        $this->assertContains('og_image', $names, 'Form must have a shared og_image field');

        // og_image must NOT appear per-locale (no og_image.ca, og_image.es, etc.)
        $this->assertNotContains('og_image.ca', $names, 'og_image must NOT be per-locale');
        $this->assertNotContains('og_image.es', $names, 'og_image must NOT be per-locale');
        $this->assertNotContains('og_image.en', $names, 'og_image must NOT be per-locale');
    }

    // ---------------------------------------------------------------------------
    // Section B — Save persists to correct SiteSetting keys
    // ---------------------------------------------------------------------------

    /**
     * Proves that save() stores title/description per locale AND og_image
     * under the correct SiteSetting keys (seo.global.{locale}.{field}).
     *
     * This test exercises the save() method directly by setting $page->data
     * and calling save().
     *
     * @test
     */
    public function test_seo_settings_page_save_persists_to_site_setting_keys(): void
    {
        $page       = new SeoSettingsPage();
        $page->data = [
            'title'       => ['ca' => 'Títol CA', 'es' => 'Título ES', 'en' => 'Title EN'],
            'description' => ['ca' => 'Desc CA', 'es' => 'Desc ES', 'en' => 'Desc EN'],
            'og_image'    => 'https://cdn.agc.com/og.jpg',
        ];

        $page->save();

        $this->assertSame('Títol CA', SiteSetting::get('seo.global.ca.title'));
        $this->assertSame('Título ES', SiteSetting::get('seo.global.es.title'));
        $this->assertSame('Title EN', SiteSetting::get('seo.global.en.title'));
        $this->assertSame('Desc CA', SiteSetting::get('seo.global.ca.description'));
        $this->assertSame('https://cdn.agc.com/og.jpg', SiteSetting::get('seo.global.og_image'));
    }

    // ---------------------------------------------------------------------------
    // Section C — HTTP smoke test: page is accessible to admin users
    // ---------------------------------------------------------------------------

    /**
     * Proves the page is registered in the Filament panel and returns 200
     * for authenticated admin users.
     *
     * @test
     */
    public function test_seo_settings_page_is_accessible_to_admin_users(): void
    {
        $admin = $this->makeAdminUser();

        $response = $this->actingAs($admin)->get('/admin/seo-settings-page');

        $response->assertStatus(200);
    }
}
