# Design: Poppins Headings & Navigation Font

**Change**: `poppins-headings-nav`
**Scope**: Public site only — Filament admin excluded.

---

## Technical Approach

Three minimal, additive edits propagate Poppins to every heading and the public-site navigation through the existing `@theme --font-headline` token. No Tailwind config or `package.json` change is required: Tailwind v4 reads theme tokens from CSS, and the project already exposes `.font-headline` and `.nav-link` as named utilities. Body, cards, footer, and form labels stay on Inter because they read from `--font-body`, which is untouched.

The fallback chain intentionally keeps `Outfit` in the slot right after `Poppins`. This is a deliberate FOUT mitigation: returning visitors likely have `Outfit` cached, and Outfit is geometric-sans-serif and metric-close to Poppins, so the visible swap is gentler than jumping to `system-ui` (which is San Francisco on macOS and Segoe UI on Windows — visually distant from both).

Fonts are loaded with `<link rel="preload" as="style">` followed by `<link rel="stylesheet">`, mirroring the pattern already used in this layout for Material Symbols (see `public.blade.php:64-65`). This makes the browser discover the CSS in parallel with HTML parse; the CSS in turn references the woff2 files, so a true `<link rel="preload" as="font">` of a woff2 URL is not stable (Google Fonts generates per-UA URLs).

## Architecture Decisions

| Decision | Choice | Alternatives considered | Rationale |
|----------|--------|--------------------------|-----------|
| Where to declare the new font family | `@theme --font-headline` token in `app.css` | New `theme.fontFamily.poppins` in Tailwind config | Tailwind v4 CSS-first; no JS config to touch; cascades automatically into `h1–h6` and the `.font-headline` utility |
| Fallback chain | `Poppins, Outfit, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif` | `Poppins, system-ui, sans-serif` | Outfit is geometric-sans-serif and metric-close to Poppins — softer FOUT for returning visitors who still have Outfit cached |
| Load weights | 400, 500, 600, 700 (drop 300) | Keep 300 | Spec R6 mandates 400/500/600/700; no spec use-case for 300; one fewer file at the CDN |
| Preload strategy | `rel="preload" as="style"` of the Google Fonts CSS | `rel="preload" as="font"` of a woff2 URL | Google Fonts generates woff2 URLs per user-agent; preloading the stylesheet is the stable, project-idiomatic choice (same pattern as Material Symbols above) |
| Where to add mobile nav class | `font-headline` Tailwind utility on the 2 mobile elements | New `font-poppins` utility | Reuse existing utility; reads the new token automatically; no purge risk because utilities are declared in `@layer utilities` |
| Weights kept in URL | 400;500;600;700 | Adding 800 | No heading or nav element in the spec uses 800; keeps CDN payload lean |

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `resources/css/app.css` | Modify | Swap `--font-headline` token + add `font-family: theme(--font-headline)` to `.nav-link` |
| `resources/views/layouts/public.blade.php` | Modify | Replace single Outfit `<link>` with a `<link rel="preload" as="style">` + Poppins `<link rel="stylesheet">` pair |
| `resources/views/public/components/navbar.blade.php` | Modify | Add `font-headline` to 2 mobile nav elements (dropdown toggle button, plain link) |

`tailwind.config.js`, `package.json`, `pnpm-lock.yaml`, Vite config, and Filament admin are all untouched.

## Patches (apply in this order)

### Patch 1 — `resources/css/app.css` line 8 (token swap)

```diff
 @theme {
     /* === Fonts === */
-    --font-headline: "Outfit", sans-serif;
+    --font-headline: "Poppins", "Outfit", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
     --font-body:     "Inter", sans-serif;
```

### Patch 2 — `resources/css/app.css` lines 150-154 (`.nav-link` font-family)

```diff
     /* Nav link */
     .nav-link {
         @apply text-[#64748B] hover:text-[#00346f] font-medium
                text-[15px] tracking-[0.02em]
                transition-colors duration-300;
+        font-family: theme(--font-headline);
     }
```

### Patch 3 — `resources/views/layouts/public.blade.php` line 61 (Google Fonts URL + preload)

```diff
     {{-- Fonts --}}
-    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400;1,600&display=swap" rel="stylesheet">
+    {{-- Fonts: Poppins (headings/nav) · Inter (body) · Playfair Display (accents) --}}
+    <link rel="preload" as="style"
+          href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400;1,600&display=swap">
+    <link rel="stylesheet"
+          href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400;1,600&display=swap">
```

### Patch 4 — `resources/views/public/components/navbar.blade.php` line 359 (mobile dropdown toggle)

```diff
                             <button @click="mobDropdownOpen = !mobDropdownOpen"
-                                    class="flex items-center justify-between w-full py-2.5 text-[#1E293B] hover:text-[#00346f] font-medium transition-colors">
+                                    class="flex items-center justify-between w-full py-2.5 text-[#1E293B] hover:text-[#00346f] font-medium font-headline transition-colors">
```

### Patch 5 — `resources/views/public/components/navbar.blade.php` line 382 (mobile plain link)

```diff
                         <a href="{{ LaravelLocalization::getLocalizedURL(app()->getLocale(), $item->url_path) }}"
                            target="{{ $item->target }}"
-                           class="block py-2.5 text-[#1E293B] hover:text-[#00346f] font-medium transition-colors">
+                           class="block py-2.5 text-[#1E293B] hover:text-[#00346f] font-medium font-headline transition-colors">
```

## Data Flow

No data flow. This is a pure presentation-layer change. Render pipeline is unchanged: route → controller → Blade → `@vite` CSS → browser. The browser's CSS engine reads the new token and applies Poppins to the cascade roots.

```
Browser GET /
   │
   ├─ HTML parse: discovers <link rel="preload" as="style"> for Google Fonts CSS
   │   └─ parallel fetch of fonts.googleapis.com (already preconnected, line 54)
   │
   ├─ HTML parse: discovers <link rel="stylesheet"> for Google Fonts CSS
   │   └─ second request, hits browser cache from preload
   │
   ├─ HTML parse: @vite injects /build/assets/app-XXXX.css
   │   └─ @theme --font-headline resolves to "Poppins", "Outfit", ...
   │
   └─ Render:
        ├─ h1–h6  → font-family: theme(--font-headline)   → Poppins  ✓
        ├─ .nav-link (desktop) → + font-family: theme(--font-headline)  → Poppins  ✓
        ├─ mobile <a>, mobile <button>  → class="font-headline"  → Poppins  ✓
        └─ <p>, cards, footer, form labels → font-family: theme(--font-body) → Inter  ✓
```

## Testing Strategy

| Layer | What to Test | Approach |
|-------|--------------|----------|
| Manual (DevTools) | DevTools → Elements → Computed → `font-family` on `h1`, `h2`, `.nav-link`, mobile `<a>`, `<p>` | Should show `"Poppins", "Outfit", system-ui, ...` on headings and nav, `"Inter", sans-serif` on `<p>` |
| Manual (visual) | Desktop (≥1024px) nav items render in Poppins | Hard refresh (Ctrl+Shift+R) at least once after first deploy to flush Outfit cache |
| Manual (mobile) | Open mobile menu → top-level labels and toggle buttons render in Poppins | iPhone Safari + Android Chrome |
| Manual (CLS) | No visible text reflow | Chrome DevTools → Rendering → "Layout Shift Regions" |
| Manual (FOUT) | First visit on slow 3G — system-ui fallback is briefly visible, then Poppins swaps in | `display=swap` is already on the URL; verify no `font-display: block` anywhere |
| Manual (Lighthouse) | Performance score within ±2 pts of baseline | Run before & after deploy; commit both reports |
| Smoke | No 404s for woff2 | Network panel — all `fonts.gstatic.com` requests return 200 |

No automated test is required: this is a typography-only change with no DOM-structure or behaviour delta.

## Rollback

Three files, fully reversible. `git checkout HEAD -- <file>` on each restores Outfit end-to-end. No DB, no migration, no cache flush.

## Open Questions

None. The spec is precise and the codebase already exposes the right hooks (`@theme`, `.nav-link`, `.font-headline`).

## Notes for Apply

- Order of patches matters: apply CSS token first (Patch 1) so any intermediate render uses the right family; preload + stylesheet (Patch 3) so the new woff2 starts downloading before navigation class is added (Patches 4–5).
- The `AGENTS.md` and `PRD-AGC-Clean-Architecture.md` still mention Outfit as the headline font. The spec scopes the change to "public site CSS only" and excludes config/docs. A follow-up documentation-sync PR can update those references, but it is out of scope for this change.
