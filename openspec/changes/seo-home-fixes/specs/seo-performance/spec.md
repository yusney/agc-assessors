# seo-performance Specification

## Purpose

Remove Leaflet CSS and JS from the synchronous `<head>` render path so the map library no
longer blocks first paint on pages that do not display a map. The map MUST still initialise
correctly when the offices map section is visible.

---

## Requirements

### Requirement: Leaflet Assets Absent from `<head>` on Non-Map Pages

Leaflet CSS (`<link>` for leaflet.css`) and JS (`<script src="...leaflet.js">`) MUST NOT
appear in the synchronous `<head>` render of pages that do not include the offices map.

#### Scenario: Home page — no map section

- GIVEN the home page does not include the offices map partial
- WHEN the HTML is returned
- THEN `<head>` MUST NOT contain `<link ... leaflet.css`
- AND `<head>` MUST NOT contain `<script ... leaflet.js`

#### Scenario: Offices page — Leaflet loaded asynchronously

- GIVEN the offices/map page is requested
- WHEN the HTML is returned
- THEN Leaflet CSS MUST be pushed via `@push('styles')` and rendered at the end of `<body>` (or via a deferred style injection)
- AND Leaflet JS MUST be pushed via `@push('scripts')` with `defer` attribute
- AND the `<head>` MUST NOT contain synchronous Leaflet `<link>` or `<script>` tags

---

### Requirement: Map Initialises After Leaflet JS Loads

The `L.map()` initialisation call MUST be guarded so it only runs after the Leaflet script
has loaded and the DOM element is available.

#### Scenario: Map renders correctly after defer

- GIVEN the offices map page loads with deferred Leaflet JS
- WHEN the `DOMContentLoaded` event fires
- THEN `L.map()` MUST initialise without `ReferenceError: L is not defined`
- AND map tiles MUST display correctly in the browser

#### Scenario: Map container initially hidden or offscreen

- GIVEN the map `<div>` has a valid `id` attribute
- WHEN the `IntersectionObserver` fires on the map container becoming visible
- THEN `L.map()` MUST be called exactly once
- AND subsequent scroll events MUST NOT re-initialise the map

---

### Requirement: Preconnect Hints Added to `<head>`

The base layout MUST include `<link rel="preconnect">` hints for external origins used by
the public frontend (Google Fonts, tile servers, etc.) to reduce DNS + TCP setup latency.

#### Scenario: Google Fonts preconnect present

- GIVEN the layout renders
- WHEN the `<head>` is inspected
- THEN `<link rel="preconnect" href="https://fonts.googleapis.com">` MUST be present
- AND `<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>` MUST be present

#### Scenario: Preconnect hints appear before font `<link>` tags

- GIVEN the layout renders
- WHEN the HTML source is read top-to-bottom
- THEN all `<link rel="preconnect">` elements MUST appear before the corresponding
  `<link rel="stylesheet">` elements that load from the same origin

---

## Validation

| Check | Method |
|---|---|
| No sync Leaflet in `<head>` | `curl -s https://agcassessors.com/ \| grep -i leaflet` → must be empty |
| Leaflet present on map page | `curl -s https://agcassessors.com/contacte \| grep -i leaflet` |
| Map renders | Manual: visit offices/map page; inspect map tiles load |
| No JS error | Browser console must show zero errors on map page load |
| Preconnect present | `curl -s https://agcassessors.com/ \| grep 'preconnect'` |
| Lighthouse Performance | Run Lighthouse on home; verify "Eliminate render-blocking resources" no longer lists Leaflet |

---

## Dependencies

- `resources/views/public/home-sections/offices_map.blade.php` — source of Leaflet tags to move
- `resources/views/layouts/public.blade.php` — must define `@stack('styles')` and `@stack('scripts')`
- Browser `IntersectionObserver` API — available in all supported browsers (no polyfill needed)
- Alpine.js — already loaded; can be used for the init guard if preferred over vanilla JS
