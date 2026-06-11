# Delta for service-description-editor

## MODIFIED Requirements

### Requirement: Service description editors MUST use Curator's native RichEditor plugin for image insertion

`AGC\Filament\Resources\ServiceResource::form()` MUST register `Awcodes\Curator\Components\Forms\RichEditor\AttachCuratorMediaPlugin` on every RichEditor field used for the service description (`description.ca`, `description.es`, `description.en`).

#### Scenario: All three description fields have the Curator plugin registered

- GIVEN `ServiceResource::form()` is invoked
- WHEN the schema for each of the three description fields is inspected
- THEN each `RichEditor` MUST have `AttachCuratorMediaPlugin` in its `getPlugins()` result

#### Scenario: Import for the plugin is present

- GIVEN `ServiceResource.php` is parsed
- WHEN the `use` statements are listed
- THEN `use Awcodes\Curator\Components\Forms\RichEditor\AttachCuratorMediaPlugin;` MUST be present
