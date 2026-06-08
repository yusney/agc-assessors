# Proposal: Poppins Headings & Navigation Font

## Intent

Replace the heading font (Outfit → Poppins) on the public site. Apply Poppins to `h1`–`h6` and the main navigation menu. Keep body text (Inter) untouched. Filament admin is out of scope.

## Scope

### In Scope
- All `h1`–`h6` and `.font-headline` elements — via single token swap (automatic cascade)
- Desktop `.nav-link` elements — via one CSS rule addition
- Mobile nav top-level `<a>` labels and dropdown `<button>` labels in navbar
- Google Fonts URL update (Outfit → Poppins) + `rel="preload"` hint

### Out of Scope
- Body text, cards, footer, form labels — remain Inter
- Filament admin panel
- Dropdown sub-item text (treated as body-level text)

## Capabilities

### New Capabilities
None

### Modified Capabilities
None — pure visual/CSS change; no functional or spec-level behaviour change.

## Approach

1. **Token swap** (`app.css` line 8): `"Outfit"` → `"Poppins", system-ui, sans-serif`
2. **Nav font** (`app.css` `.nav-link` block): add `font-family: theme(--font-headline)`
3. **Google Fonts URL** (`public.blade.php` line 61): replace `Outfit` with `Poppins:wght@400;500;600;700`; add `<link rel="preload" as="style">` sibling
4. **Mobile nav** (`navbar.blade.php`): add `font-headline` class to 2 mobile label elements (top-level `<a>` + dropdown toggle `<button>`)

Headings in home-section templates need no edits — they inherit the token automatically.

## Affected Areas

| File | Impact | Change |
|------|--------|--------|
| `resources/css/app.css` | Modified | Token (`--font-headline`) + `.nav-link` font-family — ~3 lines |
| `resources/views/layouts/public.blade.php` | Modified | Google Fonts URL + preload hint — ~3 lines |
| `resources/views/public/components/navbar.blade.php` | Modified | `font-headline` on 2 mobile nav elements — ~2 lines |

**Total estimated changed lines: ~8** (additions + modifications)

## CSS Snippets

### `resources/css/app.css` — token (line 8)
```css
--font-headline: "Poppins", system-ui, sans-serif; /* replaces "Outfit", sans-serif */
```

### `resources/css/app.css` — `.nav-link` block (add one line)
```css
.nav-link {
    @apply text-[#64748B] hover:text-[#00346f] font-medium
           text-[15px] tracking-[0.02em]
           transition-colors duration-300;
    font-family: theme(--font-headline); /* ← ADD */
}
```

### `resources/views/layouts/public.blade.php` — replace line 61 with
```html
{{-- Fonts: Poppins (headings/nav) · Inter (body) · Playfair Display (accents) --}}
<link rel="preload" as="style"
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400;1,600&display=swap">
<link rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400;1,600&display=swap">
```

> Fallback stack: `"Poppins", system-ui, sans-serif` — renders system-ui (San Francisco / Segoe UI) while Poppins loads.
> Weights loaded: 400 (normal), 500 (medium), 600 (semibold), 700 (bold) — covers all heading and nav usages.

### `resources/views/public/components/navbar.blade.php` — 2 mobile elements
```html
{{-- Mobile dropdown toggle button (~line 359): add font-headline --}}
class="flex items-center justify-between w-full py-2.5 text-[#1E293B]
       hover:text-[#00346f] font-medium font-headline transition-colors"

{{-- Mobile plain link (~line 382): add font-headline --}}
class="block py-2.5 text-[#1E293B] hover:text-[#00346f] font-medium font-headline transition-colors"
```

## Implementation Plan

| Step | Action | File | Lines |
|------|--------|------|-------|
| 1 | Update Google Fonts URL + add preload hint | `public.blade.php` | ~3 |
| 2 | Swap `--font-headline` token to Poppins | `app.css` | 1 |
| 3 | Add `font-family` to `.nav-link` | `app.css` | 1 |
| 4 | Add `font-headline` to 2 mobile nav elements | `navbar.blade.php` | 2 |
| 5 | Run `rg "Outfit"` to check for stray hardcoded references | — | — |
| 6 | Visual smoke test: headings, nav, body paragraphs | Browser | — |

**Estimated changed lines: ~8 additions/modifications** across 3 files.

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| FOUT — Poppins uncached on first visit | Low | `display=swap` + preload hint already established pattern in this layout |
| Poppins slightly wider than Outfit — heading reflow | Low | Same weight scale; no size changes; existing `text-*` classes unchanged |
| Stray `font-family: "Outfit"` hardcoded in other templates | Low | Apply phase: grep check before removing Outfit from CDN URL |
| `font-headline` utility not purged by Tailwind | None | Utility is manually declared in `@layer utilities` — never purged |

## Rollback Plan

Revert 3 files (all changes are additive/substitutive — no DB, migration, or structural impact):
1. `app.css` line 8 → restore `"Outfit", sans-serif`
2. `app.css` `.nav-link` block → remove `font-family` line
3. `public.blade.php` line 61 → restore original Google Fonts URL (Outfit weights)
4. `navbar.blade.php` → remove `font-headline` from 2 elements

## Dependencies

- Google Fonts CDN — `preconnect` to `fonts.googleapis.com` and `fonts.gstatic.com crossorigin` already present in `<head>` ✅
- `font-headline` utility already defined in `@layer utilities` ✅

## Success Criteria

- [ ] All `h1`–`h6` on public pages render in Poppins (DevTools font inspector)
- [ ] Desktop `.nav-link` items render in Poppins
- [ ] Mobile nav label elements render in Poppins
- [ ] Body paragraphs (`<p>`) still render in Inter
- [ ] No unexpected CLS (Chrome DevTools Rendering panel)
- [ ] Lighthouse Performance score unchanged (±2 points)
