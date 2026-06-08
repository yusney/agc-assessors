# Spec: Public Site Typography — Poppins Headings & Navigation

**Change**: poppins-headings-nav
**Scope**: Public site only (Filament admin excluded)

---

## Requirements

| ID | Requirement | Strength |
|----|-------------|----------|
| R1 | `h1`–`h6` and `.font-headline` elements on public pages MUST render in Poppins | MUST |
| R2 | Desktop `.nav-link` elements MUST render in Poppins | MUST |
| R3 | Mobile nav top-level `<a>` labels and dropdown `<button>` labels MUST render in Poppins | MUST |
| R4 | Body text (`<p>`, cards, footer, form labels) MUST remain in Inter — no change | MUST NOT change |
| R5 | Public `<head>` MUST include `<link rel="preload" as="style">` for the Poppins Google Fonts URL, placed before the `<link rel="stylesheet">` | MUST |
| R6 | Google Fonts URL MUST load Poppins weights 400, 500, 600, 700 with `display=swap` | MUST |
| R7 | Outfit MUST NOT appear in any CDN `<link>` in the public layout `<head>` after the change | MUST NOT |
| R8 | Filament admin panel MUST NOT be modified or affected | MUST NOT |

---

## Acceptance Criteria

| ID | Pass Condition | Fail Condition |
|----|---------------|----------------|
| AC1 | DevTools Font Inspector shows "Poppins" on any `h1`–`h6` on public pages | Font Inspector shows "Outfit", "Inter", or a system fallback permanently |
| AC2 | DevTools shows "Poppins" on `.nav-link` elements (desktop ≥1024px) | Nav links render in Inter |
| AC3 | Mobile menu items (top-level links + toggle buttons) render in Poppins after open | Mobile items remain in Inter |
| AC4 | DevTools shows "Inter" on `<p>` elements sitewide | Body text renders in Poppins |
| AC5 | `<head>` source contains `rel="preload" as="style"` for the Google Fonts URL | No preload hint present |
| AC6 | Google Fonts `<link>` URL contains `Poppins:wght@400;500;600;700` and `display=swap` | URL still references Outfit |
| AC7 | No visible layout shift from font-swap (CLS ≈ 0, verified in Chrome Rendering panel) | Visible text reflow disrupts reading flow |
| AC8 | Lighthouse Performance score within ±2 pts of pre-change baseline | Score drops > 2 points |

---

## UX Scenarios (Manual Verification)

#### Scenario: Desktop — cached font

- GIVEN Chrome desktop (≥1024px), Poppins previously cached
- WHEN loading any public page
- THEN `h1`–`h6` and `.nav-link` render in Poppins; `<p>` renders in Inter

#### Scenario: First visit / slow network (FOUT)

- GIVEN Chrome DevTools Network throttled to "Slow 3G", cache cleared
- WHEN loading any public page
- THEN headings and nav first show `system-ui` fallback, then swap to Poppins (FOUT acceptable)
- AND no content is obscured or layout-shifted beyond tolerance

#### Scenario: Mobile — nav menu open

- GIVEN mobile viewport (375px), Chrome DevTools
- WHEN tapping the hamburger/menu button to open mobile nav
- THEN top-level `<a>` labels and dropdown toggle `<button>` labels display in Poppins
- AND dropdown sub-items remain in Inter

#### Scenario: Hard refresh (cache bypass)

- GIVEN any public page open in browser
- WHEN performing Ctrl+Shift+R (hard refresh)
- THEN Poppins loads via preload hint; headings and nav render in Poppins after brief FOUT

#### Scenario: Cross-browser smoke check

- GIVEN Firefox and Safari on desktop
- WHEN loading the public homepage
- THEN headings and nav render in Poppins; body paragraphs remain in Inter

---

## Non-Functional Constraints

| Constraint | Rule |
|------------|------|
| CSS file | All CSS changes MUST be in `resources/css/app.css` only |
| No config change | `tailwind.config.js` and Vite config MUST NOT be modified |
| No package changes | `package.json` and `pnpm-lock.yaml` MUST NOT change |
| Font weights | Load 400 (normal), 500 (medium), 600 (semibold), 700 (bold) only |
| Admin isolation | Filament admin panel CSS MUST NOT be touched |
| FOUT | Flash of Unstyled Text is acceptable via `font-display: swap` |

---

## Files to Change

| File | Change Description | Lines Est. |
|------|--------------------|-----------|
| `resources/css/app.css` | Swap `--font-headline` token from `"Outfit"` to `"Poppins", system-ui, sans-serif`; add `font-family: theme(--font-headline)` to `.nav-link` block | ~2 |
| `resources/views/layouts/public.blade.php` | Replace Google Fonts `<link>` (Outfit → Poppins weights); add sibling `<link rel="preload" as="style">` | ~3 |
| `resources/views/public/components/navbar.blade.php` | Add `font-headline` Tailwind utility class to 2 mobile nav elements (~line 359 toggle button, ~line 382 plain link) | ~2 |

**Total estimated lines: ~7** (additions + substitutions across 3 files)

---

## Rollback Plan

1. `resources/css/app.css` — restore `--font-headline: "Outfit", sans-serif`; remove `font-family` line from `.nav-link`
2. `resources/views/layouts/public.blade.php` — restore original Google Fonts `<link>` with Outfit weights; remove preload hint
3. `resources/views/public/components/navbar.blade.php` — remove `font-headline` class from 2 mobile elements

**Migration notes**: No database changes. No package install or uninstall. All changes are CSS tokens and Blade class attributes — revert is a 3-file `git checkout HEAD -- <file>`. Safe to rollback at any point without side effects.
