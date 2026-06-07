# Tasks: Poppins Headings & Navigation Font

**Change**: `poppins-headings-nav`
**Scope**: Public site CSS + Blade templates only (3 files, 5 patches, ~7 changed lines)

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | ~7 (additions + substitutions) |
| 400-line budget risk | Low |
| Chained PRs recommended | No |
| Suggested PR title | `feat(public): switch headings and navigation to Poppins` |
| Suggested commit message | `feat(public): switch headings and navigation to Poppins` |

Decision needed before apply: No
Chained PRs recommended: No
Chain strategy: pending
400-line budget risk: Low

### Suggested Work Units

Single PR — far below the 400-line review budget. No chaining needed.

## Phase 1: CSS Foundation
- [x] 1.1 Swap `--font-headline` token in `resources/css/app.css` line 8 from `"Outfit", sans-serif` to `"Poppins", "Outfit", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif`
- [x] 1.2 Add `font-family: theme(--font-headline)` inside `.nav-link` block in `resources/css/app.css` (after line 153, before closing `}`)
  - **Verify**: `grep "font-family: theme(--font-headline)" resources/css/app.css` returns match ✅

## Phase 2: Font Loading & Markup
- [x] 2.1 Replace Outfit `<link>` in `resources/views/layouts/public.blade.php` line 61 with `<link rel="preload" as="style">` + `<link rel="stylesheet">` pair loading Poppins:wght@400;500;600;700 (keep Inter + Playfair Display)
  - **Verify**: `grep Poppins resources/views/layouts/public.blade.php` returns match ✅; `grep Outfit resources/views/layouts/public.blade.php` returns no match ✅
- [x] 2.2 Add `font-headline` class to mobile dropdown toggle `<button>` (line 359) and mobile plain `<a>` (line 382) in `resources/views/public/components/navbar.blade.php`
  - **Verify**: `grep font-headline resources/views/public/components/navbar.blade.php` returns two matches ✅

## Phase 3: Build & Manual Verification
- [ ] 3.1 Build CSS: `docker compose run --rm node sh -c "npm install -g pnpm && pnpm build"`
- [ ] 3.2 **Karma**: DevTools → Computed `font-family` on `h1`, `h2`, `.nav-link`, mobile `<a>`, mobile `<button>`, `<p>` — headings/nav show `"Poppins"`, body shows `"Inter"`
- [ ] 3.3 **Karma**: `<head>` source contains `rel="preload" as="style"` above Poppins stylesheet; URL has `Poppins:wght@400;500;600;700` and `display=swap`
- [ ] 3.4 **Karma**: Hard refresh + Slow 3G — FOUT acceptable, no CLS (Chrome Rendering → "Layout Shift Regions"), Lighthouse Performance within ±2 pts

## Rollback

```
git checkout HEAD -- resources/css/app.css resources/views/layouts/public.blade.php resources/views/public/components/navbar.blade.php
```
