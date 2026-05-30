# Offices Feature Specifications

## offices-domain Specification

### Purpose
Defines the core Office domain entity, repository interface, and its infrastructure Eloquent implementation for the offices directory.

### Requirements

#### Requirement: Office Entity Structure
The system MUST represent an Office with a unique identifier, translatable strings for name, address, and city, and specific scalar values for contact and location.

##### Scenario: Office Entity Validation
- GIVEN an active office location
- WHEN the Office entity is instantiated
- THEN it MUST hold `id`, `name` (TranslatableString), `address` (TranslatableString), `city` (TranslatableString), `phone` (?string), `email` (?string), `lat` (float), `lng` (float), and `isActive` (bool).

#### Requirement: Office Repository Interface
The system MUST provide an interface for retrieving office entities from the persistence layer.

##### Scenario: Fetching Active Offices
- GIVEN multiple offices where some are active and some are inactive
- WHEN `OfficeRepositoryInterface::findAllActive()` is called
- THEN it MUST return an array containing only the active Office entities.

#### Requirement: Eloquent Model and Mapper
The system MUST map the Office domain entity to an Eloquent model (`EloquentOffice`) that handles database persistence.

##### Scenario: Eloquent Model Translations
- GIVEN an `EloquentOffice` model instance
- WHEN saving or retrieving `name`, `address`, and `city`
- THEN it MUST use `spatie/laravel-translatable` to manage JSON translation mappings.


## offices-admin Specification

### Purpose
Defines the Filament 5 admin resource for managing offices.

### Requirements

#### Requirement: Office Resource Listing
The system MUST provide a Filament 5 list view for the OfficeResource.

##### Scenario: Viewing the Offices Table
- GIVEN an admin is authenticated
- WHEN they navigate to the Offices section in Filament
- THEN they MUST see a table containing the `name`, `city`, and `is_active` toggle.

#### Requirement: Office Resource Form
The system MUST provide a create and edit form for offices with localized fields.

##### Scenario: Editing an Office with Translations
- GIVEN an admin is creating or editing an office
- WHEN the form is displayed
- THEN it MUST show language tabs (CA / ES / EN) for `name`, `address`, and `city`
- AND it MUST show shared unlocalized fields for `phone`, `email`, `lat`, `lng`, and `is_active` toggle.

#### Requirement: Filament 5 Strict Namespaces
The system MUST strictly adhere to Filament 5 namespaces for the OfficeResource.

##### Scenario: Resource Implementation
- GIVEN the `OfficeResource` class
- WHEN implemented
- THEN it MUST be a `final class`
- AND use `declare(strict_types=1)`
- AND utilize `Filament\Schemas\Schema` and `Filament\Actions\*` instead of deprecated namespaces.


## offices-public-page Specification

### Purpose
Defines the public-facing listing page for offices, containing a Google Map embed and a grid of office cards.

### Requirements

#### Requirement: Localized Offices Routes
The system MUST expose the offices page via localized URLs.

##### Scenario: Accessing Offices in Different Languages
- GIVEN the application supports `ca`, `es`, and `en`
- WHEN a user navigates to `/oficines`, `/es/oficinas`, or `/en/offices`
- THEN the system MUST route the request to `OfficesController@index` with the correct active locale.

#### Requirement: Public View Rendering
The system MUST render a map and an accompanying card grid for all active offices.

##### Scenario: Map and Card Grid Display
- GIVEN there are active offices in the repository
- WHEN `OfficesController@index` is accessed
- THEN it MUST retrieve all active offices
- AND pass JSON-encoded coordinates and the full collection to the Blade view
- AND the view MUST render a Google Maps embed with a min-height of 400px and markers
- AND the view MUST render a card for each office containing its `name`, `address`, `city`, `phone`, `email`, and a "Como llegar" link.


## offices-home-section Specification

### Purpose
Defines a partial Blade view to inject an interactive offices map into the dynamic home sections.

### Requirements

#### Requirement: Render Offices Map Home Section
The system MUST provide a valid section type `offices_map` capable of displaying a compact map and featured offices.

##### Scenario: Home Page Dynamic Zone Rendering
- GIVEN the `HomeSectionType::OFFICES_MAP` is configured in the admin
- WHEN the home page renders the section
- THEN it MUST load `resources/views/public/home-sections/offices_map.blade.php`
- AND it MUST display the section `title`, `subtitle`, and a CTA button to the full offices page
- AND it MUST render a compact map (min-height 350px)
- AND it MUST show a maximum of 3 office cards if a limit setting is present, or all active offices otherwise.


## Delta for navigation

## ADDED Requirements

### Requirement: Offices Navigation Link
The system MUST include a seeded navigation link to the offices public page in the top menu.

#### Scenario: Seeding the Navigation Menu
- GIVEN the `MenuItemSeeder` is executed
- WHEN seeding the primary navigation menu
- THEN it MUST add a new record pointing to the localized offices URL using `LaravelLocalization::getLocalizedURL()`
- AND the link label MUST be driven by the translation files in `resources/lang/{ca,es,en}/messages.php`.


## Delta for services config

## ADDED Requirements

### Requirement: Google Maps Configuration
The system MUST securely provision the Google Maps API key via application services configuration.

#### Scenario: Environment Variable Binding
- GIVEN the application environment contains `GOOGLE_MAPS_API_KEY`
- WHEN the `config/services.php` file is loaded
- THEN it MUST expose the key under `services.google_maps.key`
- AND the application MUST use this config value to render frontend maps instead of reading the `.env` file directly.