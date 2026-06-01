# seo-accessibility Specification

## Purpose

Fix the stats section's missing heading landmark so screen readers and assistive technologies
can navigate the home page without skipping a major content region. Ensures the page passes
an axe-core audit with no critical violations on heading continuity.

---

## Requirements

### Requirement: Stats Section Has a Visually-Hidden `<h2>` Heading

The stats section in `home-sections/stats.blade.php` MUST include an `<h2>` element with
the Tailwind utility class `sr-only` so the heading is announced by screen readers but
invisible in the visual layout.

#### Scenario: Heading present in rendered HTML

- GIVEN the home page is requested
- WHEN the stats section is rendered
- THEN the DOM MUST contain `<h2 class="sr-only">` (or `class` including `sr-only`)
- AND the heading text MUST be the translated value of `messages.stats_heading`

#### Scenario: Heading text is translated per locale

- GIVEN the active locale is `ca`
- WHEN the stats section renders
- THEN the `<h2>` text MUST equal the Catalan translation of `messages.stats_heading`

- GIVEN the active locale is `es`
- WHEN the stats section renders
- THEN the `<h2>` text MUST equal the Spanish translation

- GIVEN the active locale is `en`
- WHEN the stats section renders
- THEN the `<h2>` text MUST equal the English translation

---

### Requirement: Translation Key `stats_heading` Added in All Three Locales

`resources/lang/{ca,es,en}/messages.php` MUST each include a `stats_heading` key with a
descriptive, human-readable label.

#### Scenario: Translation key defined

- GIVEN the application bootstraps
- WHEN `__('messages.stats_heading')` is called in any of the three locales
- THEN the return value MUST NOT be `'messages.stats_heading'` (i.e., the key must be found)
- AND the value MUST be a non-empty string

---

### Requirement: Heading Level Continuity Maintained

The `<h2>` in the stats section MUST NOT break the heading hierarchy. It MUST appear after
the section's preceding `<h1>` (hero headline) and before any `<h3>` elements within stats.

#### Scenario: axe-core heading order audit passes

- GIVEN the home page DOM is loaded
- WHEN axe-core rule `heading-order` runs
- THEN zero violations MUST be reported for the stats section

---

## Validation

| Check | Method |
|---|---|
| `<h2 class="sr-only">` in HTML | `curl -s https://agcassessors.com/ \| grep 'sr-only'` |
| Translation key set in `ca` | `php artisan tinker --execute="app()->setLocale('ca'); echo __('messages.stats_heading');"` |
| axe-core audit | Lighthouse Accessibility audit → heading-order rule |
| Visually hidden | Screenshot comparison — heading must not alter visual layout |

---

## Dependencies

- `resources/lang/{ca,es,en}/messages.php` — all three files must be modified
- `resources/views/public/home-sections/stats.blade.php` — add `<h2>`
- Tailwind `sr-only` utility — already available via Tailwind 4 in `app.css`
