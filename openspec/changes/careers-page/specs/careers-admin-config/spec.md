# Delta for careers-admin-config

## ADDED Requirements

### Requirement: WorkWithUsSettingsPage Filament Page

The admin panel MUST provide a `WorkWithUsSettingsPage` that allows editing all
"Trabaja con nosotros" page content in ca/es/en locales. Data MUST be persisted
via `SiteSetting::set('careers_page', $data)` as a JSON blob.

#### Scenario: Admin opens the settings page for the first time

- GIVEN the `careers_page` key does not exist in `site_settings`
- WHEN the admin navigates to the WorkWithUsSettingsPage
- THEN the form MUST load with all fields empty (no crash, no 500)

#### Scenario: Admin saves valid content

- GIVEN all required fields in the hero section have values in at least one locale
- WHEN the admin clicks "Guardar"
- THEN `SiteSetting::set('careers_page', $data)` MUST be called
- AND a Filament success notification MUST appear
- AND reloading the page MUST show the saved values

#### Scenario: Admin saves partial locale content

- GIVEN the hero title is filled for `ca` but left empty for `es` and `en`
- WHEN the admin saves
- THEN the save MUST succeed (empty strings are valid)
- AND the public page MUST fall back to `ca` for empty locales

---

### Requirement: Hero Section Fields

The settings page MUST expose the following hero fields, all translatable (ca/es/en):
`hero_title`, `hero_subtitle`, `hero_cta_text`, `hero_cta_url` (non-translatable URL),
`hero_image` (file upload, non-translatable).

#### Scenario: Hero image upload

- GIVEN the admin uploads a valid JPG/PNG image
- WHEN the form is saved
- THEN the image MUST be stored via Filament's `FileUpload` component
- AND the image path MUST be persisted inside the `careers_page` JSON

#### Scenario: Hero image missing

- GIVEN `hero_image` is null in `site_settings`
- WHEN the public page renders
- THEN the hero section MUST render without an `<img>` tag (no broken image)

---

### Requirement: Benefits Section (3 Fixed Cards)

The settings page MUST expose exactly 3 benefit cards via a Filament `Repeater`
locked to min/max 3 items. Each card MUST have: `icon` (text input for icon name),
`title` (translatable), `description` (translatable).

#### Scenario: Reorder benefits

- GIVEN 3 benefits are saved
- WHEN the admin drags card 3 to position 1 and saves
- THEN `SiteSetting::get('careers_page')['benefits']` MUST reflect the new order
- AND the public page MUST render benefits in the updated order

#### Scenario: Admin tries to add a 4th benefit

- GIVEN 3 benefits already exist in the Repeater
- WHEN the admin clicks the Repeater's "Add" button
- THEN the button MUST be disabled (Repeater `maxItems(3)`)

---

### Requirement: Form Section Fields

The settings page MUST expose translatable fields: `form_intro`, `form_privacy_text`,
`form_success_message`, and a single non-translatable `form_destination_email`.

#### Scenario: Destination email validation

- GIVEN the admin enters an invalid string like "not-an-email"
- WHEN the admin saves
- THEN Filament MUST display an inline validation error on `form_destination_email`
- AND the form MUST NOT be saved

---

### Requirement: Footer CTA Fields

The settings page MUST expose `footer_cta_title` (translatable) and
`footer_cta_button_text` (translatable).

#### Scenario: Footer CTA fields save correctly

- GIVEN all footer CTA fields are filled in all 3 locales
- WHEN the admin saves
- THEN `SiteSetting::get('careers_page')['footer_cta_title']` MUST equal
  `{'ca': '...', 'es': '...', 'en': '...'}`

---

### Requirement: Navigation Registration

`WorkWithUsSettingsPage` MUST appear in the Filament sidebar under the
`Configuración` navigation group.

#### Scenario: Page visible in sidebar

- GIVEN an authenticated admin user
- WHEN the admin panel loads
- THEN "Trabaja con nosotros" (or equivalent label) MUST appear under "Configuración"

---

## Validation

```bash
# Syntax
docker compose exec php php -l src/Filament/Pages/WorkWithUsSettingsPage.php

# PHPStan
docker compose exec php vendor/bin/phpstan analyse src/Filament/Pages/WorkWithUsSettingsPage.php --memory-limit=1G

# Manual: navigate to http://localhost:8080/admin, save form, check DB
docker compose exec php php artisan tinker --execute="dump(\AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting::get('careers_page'));"
```
