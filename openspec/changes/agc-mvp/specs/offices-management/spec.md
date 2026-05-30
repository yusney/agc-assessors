# Offices Management Specification

## Purpose
Defines the entity, seeders, and translations for the 6 company offices.

## Requirements

### Requirement: Soft Constraint Seeded Offices
The system MUST initially seed exactly 6 offices. The application MUST NOT hard-code a limit preventing administrators from adding more.

#### Scenario: Running fresh migrations and seeds
- GIVEN an empty database
- WHEN `php artisan db:seed` is executed
- THEN exactly 6 office records MUST be created with complete contact info and map links.

### Requirement: Office Translatable Data
The system MUST support translatable descriptions and localized contact information for each office.

#### Scenario: Viewing an office in English
- GIVEN the active locale is `en`
- WHEN the user views the office details
- THEN the system MUST display the English description (falling back to `es` then `ca` if missing).