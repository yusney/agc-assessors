# Delta for careers-frontend-page

## ADDED Requirements

### Requirement: Localized Public Routes

The system MUST register GET routes for the careers page at three locale-specific URLs:
- `ca` (default, no prefix): `/treballa-amb-nosaltres`
- `es`: `/es/trabaja-con-nosotros`
- `en`: `/en/work-with-us`

Route keys MUST be added to `resources/lang/{ca,es,en}/routes.php` using the key
`careers`.

#### Scenario: Catalan URL resolves

- GIVEN the application locale is `ca`
- WHEN a GET request is made to `/treballa-amb-nosaltres`
- THEN the HTTP response MUST be 200
- AND the view `public.pages.work-with-us` MUST render

#### Scenario: Spanish URL resolves

- GIVEN the application locale is `es`
- WHEN a GET request is made to `/es/trabaja-con-nosotros`
- THEN the HTTP response MUST be 200

#### Scenario: English URL resolves

- GIVEN the application locale is `en`
- WHEN a GET request is made to `/en/work-with-us`
- THEN the HTTP response MUST be 200

#### Scenario: Unknown locale returns 404

- GIVEN a request to `/fr/travaillez-avec-nous`
- WHEN the router processes it
- THEN the response MUST be 404

---

### Requirement: Settings-Driven Content

The public page controller MUST load all content via
`SiteSetting::get('careers_page', [])`. The view MUST NOT hard-code any copy text.
All translatable fields MUST be resolved using the active locale.

#### Scenario: Content renders from settings

- GIVEN `SiteSetting::get('careers_page')` returns a populated array
- WHEN the page renders with locale `es`
- THEN the hero title MUST match `settings['hero_title']['es']`
- AND the benefits grid MUST render 3 cards from `settings['benefits']`

#### Scenario: Settings not yet configured

- GIVEN `careers_page` key is absent from `site_settings`
- WHEN a user visits `/treballa-amb-nosaltres`
- THEN the page MUST render with HTTP 200 (no crash)
- AND sections with null content MUST render as empty placeholders

---

### Requirement: Hero Section Rendering

The hero section MUST render `hero_title`, `hero_subtitle`, and a CTA button
linking to `hero_cta_url` with text `hero_cta_text`. If `hero_image` is set,
MUST render a responsive `<img>` inside the hero.

#### Scenario: Hero with image

- GIVEN `hero_image` is a valid stored path
- WHEN the page renders
- THEN an `<img>` tag MUST be present with a `src` pointing to the image URL
- AND the image MUST have a non-empty `alt` attribute (derived from `hero_title`)

---

### Requirement: Benefits Grid Rendering

The page MUST render exactly the number of benefits stored in settings (up to 3)
in a responsive CSS grid. Each card MUST display icon, title, and description
resolved for the active locale.

#### Scenario: 3 benefits displayed

- GIVEN `settings['benefits']` contains 3 items with ca/es/en fields
- WHEN the page renders in locale `ca`
- THEN 3 benefit cards MUST appear in the DOM
- AND each card MUST show the `ca` locale values

---

### Requirement: Application Form Section

The page MUST render the job application form within the same view (or via a Blade
`@include`). The form intro text MUST come from `settings['form_intro']` for the
active locale. The privacy disclaimer MUST render from `settings['form_privacy_text']`.

#### Scenario: Form visible on page

- GIVEN the page renders successfully
- WHEN inspecting the HTML
- THEN a `<form>` with `method="POST"` and `action` pointing to the store route
  MUST be present
- AND a CSRF `_token` hidden input MUST be included

---

### Requirement: Footer CTA Section

The page MUST render a footer CTA block with `footer_cta_title` and a button
with text `footer_cta_button_text` that scrolls to or links to the application form.

---

### Requirement: Layout Inheritance

The page MUST extend the existing public layout (`layouts.public`) so the navbar,
trust-bar, and footer are inherited.

#### Scenario: Navbar present

- GIVEN the careers page renders
- WHEN inspecting the response body
- THEN the `<nav>` element from the shared layout MUST be present in the HTML

---

### Requirement: Responsive Design

The page MUST be usable on viewports ≥ 375px. Benefits grid MUST stack to 1 column
on mobile (< 640px), 2 columns on tablet (640–1023px), and 3 columns on desktop (≥ 1024px).

#### Scenario: Mobile layout

- GIVEN a viewport of 375px width
- WHEN the page renders
- THEN no horizontal overflow MUST occur (no scroll bar on x-axis)

---

## Validation

```bash
# Route list
docker compose exec php php artisan route:list | grep treballa

# HTTP smoke
curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/treballa-amb-nosaltres
curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/es/trabaja-con-nosotros
curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/en/work-with-us
```
