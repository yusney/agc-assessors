# Delta for news-body-editor

## MODIFIED Requirements

### Requirement: News body editors MUST use Curator's native RichEditor plugin for image insertion

`AGC\Filament\Resources\NewsResource::form()` MUST register `Awcodes\Curator\Components\Forms\RichEditor\AttachCuratorMediaPlugin` on every RichEditor field used for the news body content (`body.ca`, `body.es`, `body.en`).

#### Scenario: All three body fields have the Curator plugin registered

- GIVEN `NewsResource::form()` is invoked
- WHEN the schema for each of the three body fields is inspected
- THEN each `RichEditor` MUST have `AttachCuratorMediaPlugin` in its `getPlugins()` result

#### Scenario: Import for the plugin is present

- GIVEN `NewsResource.php` is parsed
- WHEN the `use` statements are listed
- THEN `use Awcodes\Curator\Components\Forms\RichEditor\AttachCuratorMediaPlugin;` MUST be present
