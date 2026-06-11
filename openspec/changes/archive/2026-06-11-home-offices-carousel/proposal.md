# Proposal: Home Offices Carousel

## Intent

The public home page currently renders a static 3-card grid from the 6 active offices in `offices_map.blade.php`. The user wants this converted to a horizontally-scrolling carousel that shows 3 cards on desktop, 2 on tablet, 1 on mobile — with manual navigation (arrows + dots + drag). Visual look must match the existing cards exactly (typography, spacing, primary `#00346f`, accent `#00B4D8`).

## Scope

### In Scope
- Replace the static `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3` card container in `offices_map.blade.php` with a responsive carousel component
- Carousel shows **3 cards on lg+**, **2 on md**, **1 on sm-** viewport
- Navigation: prev/next arrows + dot indicators, swipe/drag on touch
- Preserve ALL existing card markup (name, address, phone, email, CTAs, border-l accent)
- Preserve the Leaflet map and section header (title/subtitle/CTA) above the carousel
- Carousel content: all 6 active offices (remove the `array_slice` limit)

### Out of Scope
- Autoplay (product decision deferred)
- Individual office detail page changes
- Backend / domain layer changes (no new migration needed)
- Changes to the offices public page (`/oficines`)
- Filament admin changes

## Capabilities

### New Capabilities
- `home-offices-carousel`: Alpine.js-powered horizontally-scrolling carousel replacing the static grid in `offices_map.blade.php`. Shows N cards per viewport breakpoint, supports arrow + dot + drag navigation.

### Modified Capabilities
- `offices-home-section`: The `offices_map` blade partial changes from a static grid to a carousel. No spec-level behavior change — purely a presentation layer update.

## Approach

1. **Alpine.js carousel** — use a lightweight custom Alpine.js carousel component (no external lib) inside `offices_map.blade.php`. No npm package needed.
2. **Responsive viewport detection** — use Tailwind breakpoints (`lg:`, `md:`) to set `items-per-slide` as a CSS variable driven by Alpine state.
3. **Navigation** — prev/next `<button>` arrows + dot indicators rendered dynamically based on total slides. Touch/drag support via `@touchstart`/`@touchmove` on the track element.
4. **Slide calculation** — compute `Math.ceil(6 / itemsPerView)` slides; each slide is a CSS grid row with the correct column count.
5. **Preserve card markup** — the `@foreach($offices as $office)` loop stays; it will render all 6 offices inside the carousel track. The outer `.grid` wrapper becomes the carousel track with `overflow-x-auto` + `snap-x`.
6. **No backend change** — the `$offices` collection passed to the view is unchanged; no new migration, no domain changes.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `resources/views/public/home-sections/offices_map.blade.php` | Modified | Replace static 3-col grid with Alpine.js carousel. Preserve map, header, all card markup. |
| `resources/views/public/home.blade.php` | No change | Already includes `offices_map` via `@includeIf` / section dispatch |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Scroll hijack conflicts with Leaflet map scroll | Low | Use `snap-x` + `overflow-x-auto` (native scroll, no JS intercept) |
| Dots count mismatch with visible cards | Low | Compute slides server-side in Blade; pass `$slideCount` to Alpine |
| Arrow buttons accessibility (keyboard nav) | Med | Add `role="button"`, `aria-label`, and `tabindex`; support arrow-key nav in Alpine |

## Rollback Plan

1. Revert `offices_map.blade.php` to the previous static grid markup (git checkout)
2. No DB migration needed — purely presentation
3. Re-enable the `array_slice` if carousel is reverted (already in git history)

## Dependencies

- `Alpine.js` — already installed and used on the site (no new dependency)
- `Tailwind CSS 4` — already in use (CSS-based responsive breakpoints, no new plugin)

## Success Criteria

- [ ] Home page loads with carousel showing 3 offices on lg+, 2 on md, 1 on sm-
- [ ] Prev/next arrows navigate correctly through all 6 offices
- [ ] Dot indicators reflect current slide; clicking a dot jumps to that slide
- [ ] Touch/drag swipe works on mobile
- [ ] All 6 offices are visible by navigating (no cards hidden permanently)
- [ ] Card visual look (colors, typography, spacing) matches existing design
- [ ] Page renders without JS errors on both desktop and mobile
- [ ] `php artisan test` green; PHPStan level 8 passes

## Open Questions (flagged — do not block)

| # | Question | Recommended Default |
|---|----------|---------------------|
| 1 | Autoplay yes/no + interval | **No autoplay** (user said "o que puedas darle click") |
| 2 | Loop (infinite scroll) or stop at edges | **Stop at edges** (no wrap-around) |
| 3 | Dots vs arrows vs both | **Both** (arrows + dots) |
| 4 | Whether the whole card is clickable | **Yes** — "Ver oficina" link covers full card surface via absolute positioning |
| 5 | Manual sort order vs natural order | **Natural order** (by `is_active` + `id` from repository) |