# Filament Admin Specification

## Purpose
Defines the Filament 5 resources, translatable components, SEO fields, and homepage zone editor.

## Requirements

### Requirement: Clean Architecture Boundaries in Admin
Filament resources MUST interact with the database using Domain Actions and Repository contracts; direct Eloquent queries for writes MUST NOT be used in the Domain layer.

#### Scenario: Creating a new Category via Filament
- GIVEN an admin fills out the Create Category form in Filament
- WHEN they submit the form
- THEN Filament MUST utilize the `CreateCategoryAction` to perform the insertion.

### Requirement: Translatable Form Components
The system MUST provide a UI in Filament for admins to input data across all three locales (ca, es, en).

#### Scenario: Editing a translatable Service
- GIVEN an admin opens a Service resource
- WHEN they edit the title
- THEN they MUST be able to switch between locale tabs to input the Catalan, Spanish, and English titles.

### Requirement: Eager Loading for List Views
Filament List Views MUST eager load relationships to prevent N+1 query problems.

#### Scenario: Viewing the Posts list
- GIVEN 50 posts exist with author and category relationships
- WHEN the admin loads the Posts index in Filament
- THEN the underlying query MUST eager load authors and categories, keeping the query count stable.