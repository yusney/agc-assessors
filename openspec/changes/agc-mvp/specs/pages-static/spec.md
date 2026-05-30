# Static Pages Specification

## Purpose
Defines the Page CRUD for About and Legal pages (privacy, cookies, legal, usage) using anchored sections.

## Requirements

### Requirement: Manageable Legal Pages
The system MUST allow administrators to manage the content of legal pages (privacy, cookies) with translations.

#### Scenario: Updating the privacy policy
- GIVEN an administrator has updated the privacy policy content in Filament
- WHEN a user visits `/ca/legal/privacy`
- THEN the updated localized content MUST be displayed.

### Requirement: Anchored Content Sections
The system MUST support anchored sections within static pages for easy navigation (e.g., jump links in legal documents).

#### Scenario: Navigating to a specific legal clause
- GIVEN a legal page has anchored sections
- WHEN a user clicks a jump link (`#clause-3`)
- THEN the page MUST smoothly scroll to the corresponding section.