# Delta for page-content-editor

## MODIFIED Requirements

### Requirement: Page content editors MUST use Curator's native RichEditor plugin for image insertion

`AGC\Filament\Resources\PageResource::form()` MUST register `Awcodes\Curator\Components\Forms\RichEditor\AttachCuratorMediaPlugin` on every RichEditor field used for the page content (`content.ca`, `content.es`, `content.en`).

#### Scenario: All three content fields have the Curator plugin registered

- GIVEN `PageResource::form()` is invoked
- WHEN the schema for each of the three content fields is inspected
- THEN each `RichEditor` MUST have `AttachCuratorMediaPlugin` in its `getPlugins()` result

#### Scenario: Import for the plugin is present

- GIVEN `PageResource.php` is parsed
- WHEN the `use` statements are listed
- THEN `use Awcodes\Curator\Components\Forms\RichEditor\AttachCuratorMediaPlugin;` MUST be present
