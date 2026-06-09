<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Forms;

use Awcodes\Curator\Components\Forms\RichEditor\AttachCuratorMediaPlugin;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Tests\TestCase;

/**
 * Phase 1 — Plugin characterization tests.
 *
 * Documents the upstream AttachCuratorMediaPlugin API contract against
 * R1.1, R1.2, R1.3, R1.5, R1.6, R5.2 of the curator-rich-editor-image-picker spec.
 *
 * This is a characterization test — it runs GREEN immediately because the
 * plugin is already installed and working upstream. It guards against silent
 * regressions in Curator's plugin implementation.
 */
final class AttachCuratorMediaPluginTest extends TestCase
{
    // ---------------------------------------------------------------------------
    // R1.1 — Plugin class autoloads and implements RichContentPlugin
    // ---------------------------------------------------------------------------

    public function test_plugin_class_autoloads(): void
    {
        $this->assertTrue(
            class_exists('Awcodes\\Curator\\Components\\Forms\\RichEditor\\AttachCuratorMediaPlugin'),
            'AttachCuratorMediaPlugin must be loadable via Composer autoloader'
        );
    }

    public function test_plugin_implements_rich_content_plugin_contract(): void
    {
        $plugin = AttachCuratorMediaPlugin::make();

        $this->assertInstanceOf(
            RichContentPlugin::class,
            $plugin,
            'AttachCuratorMediaPlugin must implement RichContentPlugin'
        );
    }

    // ---------------------------------------------------------------------------
    // R1.2 — Plugin registers exactly one toolbar tool named attachCuratorMedia
    // ---------------------------------------------------------------------------

    public function test_plugin_provides_exactly_one_editor_tool(): void
    {
        $plugin = AttachCuratorMediaPlugin::make();
        $tools  = $plugin->getEditorTools();

        $this->assertCount(
            1,
            $tools,
            'AttachCuratorMediaPlugin must provide exactly one editor tool'
        );
    }

    public function test_toolbar_tool_is_named_attach_curator_media(): void
    {
        $plugin = AttachCuratorMediaPlugin::make();
        $tools  = $plugin->getEditorTools();

        $this->assertSame(
            'attachCuratorMedia',
            $tools[0]->getName(),
            'The toolbar tool must be named "attachCuratorMedia"'
        );
    }

    public function test_toolbar_tool_uses_photo_icon(): void
    {
        $plugin = AttachCuratorMediaPlugin::make();
        $tools  = $plugin->getEditorTools();

        $this->assertSame(
            'heroicon-o-photo',
            $tools[0]->getIcon(),
            'The toolbar tool icon must be "heroicon-o-photo"'
        );
    }

    // ---------------------------------------------------------------------------
    // R1.3 — Plugin action opens single-select CuratorPanel modal
    // ---------------------------------------------------------------------------

    public function test_plugin_provides_attach_curator_media_action(): void
    {
        $plugin  = AttachCuratorMediaPlugin::make();
        $actions = $plugin->getEditorActions();
        $names   = array_map(fn ($action) => $action->getName(), $actions);

        $this->assertContains(
            'attachCuratorMedia',
            $names,
            'Plugin must provide an action named "attachCuratorMedia"'
        );
    }

    public function test_modal_settings_use_single_select(): void
    {
        $plugin  = AttachCuratorMediaPlugin::make();
        $actions = $plugin->getEditorActions();

        $action = null;
        foreach ($actions as $a) {
            if ($a->getName() === 'attachCuratorMedia') {
                $action = $a;
                break;
            }
        }

        $this->assertNotNull($action, 'attachCuratorMedia action must exist');

        // Inspect the modalContent closure via Reflection to get the settings array.
        // The modalContent is a Closure that accepts (RichEditor $component, array $arguments)
        // and returns a View. The settings are passed to the view.
        $reflection = new \ReflectionObject($action);
        $prop       = $reflection->getProperty('modalContent');
        $prop->setAccessible(true);
        $modalContent = $prop->getValue($action);

        $this->assertInstanceOf(\Closure::class, $modalContent);

        // Invoke the closure with a mock RichEditor component to capture the settings
        $mockComponent = $this->createMock(\Filament\Forms\Components\RichEditor::class);
        $mockComponent->method('getKey')->willReturn('test-key');
        $mockComponent->method('getStatePath')->willReturn('test.state.path');
        $mockComponent->method('getFileAttachmentsAcceptedFileTypes')->willReturn(null);
        $mockComponent->method('getFileAttachmentsDirectory')->willReturn(null);
        $mockComponent->method('getFileAttachmentsDiskName')->willReturn('public');
        $mockComponent->method('getFileAttachmentsMaxSize')->willReturn(null);
        $mockComponent->method('getFileAttachmentsVisibility')->willReturn('public');

        $view = $modalContent($mockComponent, []);
        $settings = $view->getData()['settings'];

        $this->assertFalse(
            $settings['isMultiple'],
            'Modal must use single-select (isMultiple: false)'
        );
        $this->assertSame(
            1,
            $settings['maxItems'],
            'Modal must limit to 1 item (maxItems: 1)'
        );
    }

    // ---------------------------------------------------------------------------
    // R1.6 — Modal has no submit action (fire-and-forget JS interception)
    // ---------------------------------------------------------------------------

    public function test_modal_has_no_submit_action(): void
    {
        $plugin  = AttachCuratorMediaPlugin::make();
        $actions = $plugin->getEditorActions();

        $action = null;
        foreach ($actions as $a) {
            if ($a->getName() === 'attachCuratorMedia') {
                $action = $a;
                break;
            }
        }

        $this->assertNotNull($action, 'attachCuratorMedia action must exist');

        // modalSubmitAction(false) means no submit button is rendered.
        // Inspect via Reflection to avoid triggering Livewire evaluation.
        $reflection = new \ReflectionObject($action);
        $prop       = $reflection->getProperty('modalSubmitAction');
        $prop->setAccessible(true);
        $modalSubmitAction = $prop->getValue($action);

        $this->assertFalse(
            $modalSubmitAction,
            'Modal must have no submit action (modalSubmitAction: false)'
        );
    }

    public function test_modal_state_path_is_editor_state_path(): void
    {
        $plugin  = AttachCuratorMediaPlugin::make();
        $actions = $plugin->getEditorActions();

        $action = null;
        foreach ($actions as $a) {
            if ($a->getName() === 'attachCuratorMedia') {
                $action = $a;
                break;
            }
        }

        $this->assertNotNull($action, 'attachCuratorMedia action must exist');

        $reflection = new \ReflectionObject($action);
        $prop       = $reflection->getProperty('modalContent');
        $prop->setAccessible(true);
        $modalContent = $prop->getValue($action);

        $mockComponent = $this->createMock(\Filament\Forms\Components\RichEditor::class);
        $mockComponent->method('getKey')->willReturn('test-key');
        $mockComponent->method('getStatePath')->willReturn('body.ca');
        $mockComponent->method('getFileAttachmentsAcceptedFileTypes')->willReturn(null);
        $mockComponent->method('getFileAttachmentsDirectory')->willReturn(null);
        $mockComponent->method('getFileAttachmentsDiskName')->willReturn('public');
        $mockComponent->method('getFileAttachmentsMaxSize')->willReturn(null);
        $mockComponent->method('getFileAttachmentsVisibility')->willReturn('public');

        $view = $modalContent($mockComponent, []);
        $settings = $view->getData()['settings'];

        $this->assertSame(
            'body.ca',
            $settings['statePath'],
            'Modal statePath must equal the RichEditor statePath (not a new property)'
        );
    }
}