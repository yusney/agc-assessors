# Multilingual Routing Specification

## Purpose
Defines the locale routing, URL prefixes, session persistence, and fallback logic for the multilingual public website.

## Requirements

### Requirement: URL Prefixes and Locale Setting
The system MUST serve all public routes under locale prefixes (`/ca/`, `/es/`, `/en/`) and set the application locale accordingly.

#### Scenario: User visits locale-prefixed URL
- GIVEN the user navigates to `/ca/about`
- WHEN the request is processed
- THEN the application locale MUST be set to `ca`
- AND the user session MUST persist the `ca` locale preference.

### Requirement: Default Locale Redirect
The system MUST redirect users from the root URL (`/`) to their preferred or default locale.

#### Scenario: User visits root without session
- GIVEN the user has no locale stored in their session
- WHEN they visit `/`
- THEN the system MUST redirect them to `/ca/` (the default locale).

### Requirement: Locale Fallback Chain
The system MUST implement a locale fallback chain: `es` -> `ca` when translations are missing.

#### Scenario: Missing translation for requested locale
- GIVEN the application locale is `en`
- WHEN rendering a translatable field that lacks an `en` value
- THEN the system MUST attempt to display the `es` value
- AND IF the `es` value is also missing, THEN the system MUST display the `ca` value.