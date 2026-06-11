# Tasks: Home Offices Carousel

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | ~60 |
| 400-line budget risk | Low |
| Chained PRs recommended | No |
| Suggested split | Single PR |
| Delivery strategy | ask-on-risk |
| Chain strategy | pending |

Decision needed before apply: No
Chained PRs recommended: No
Chain strategy: pending
400-line budget risk: Low

---

## Phase 1: Foundation — Localization Keys + CSS Utility

### 1.1 Add Catalan carousel localization keys
- [x] Files: `resources/lang/ca/messages.php`
- Depends on: —
- Summary: Add 5 new `offices.carousel_*` keys under the existing `offices` array: `carousel_prev`, `carousel_next`, `carousel_go_to`, `carousel_drag_hint`, `carousel_label`.
- Acceptance: Keys resolve correctly in Blade with `{{ __('messages.offices.carousel_prev') }}`.
- Estimated lines: +5

### 1.2 Add Spanish carousel localization keys
- [x] Files: `resources/lang/es/messages.php`
- Depends on: —
- Summary: Same5 keys with Spanish translations (`Anterior`, `Siguiente`, `Ir a la diapositiva :slide`, `Desliza para navegar`, `Carrusel de oficinas`).
- Acceptance: Keys resolve correctly in Blade.
- Estimated lines: +5

### 1.3 Add English carousel localization keys
- [x] Files: `resources/lang/en/messages.php`
- Depends on: —
- Summary: Same 5 keys with English translations (`Previous`, `Next`, `Go to slide :slide`, `Drag to navigate`, `Offices carousel`).
- Acceptance: Keys resolve correctly in Blade.
- Estimated lines: +5

### 1.4 Add `hide-scrollbar` CSS utility
- [x] Files: `resources/css/app.css`
- Depends on: —
- Summary: Add `.hide-scrollbar` utility under `@layer utilities` with `ms-overflow-style: none`, `scrollbar-width: none`, and `-webkit-scrollbar` display none.
- Acceptance: Class can be applied to `overflow-x-auto` elements; scrollbar is hidden in Chrome/Firefox/Safari.
- Estimated lines: +8

---

## Phase 2: Core Implementation — Alpine + Blade Template

### 2.1 Rewrite `offices_map.blade.php` with Alpine carousel
- [x] Files: `resources/views/public/home-sections/offices_map.blade.php`
- Depends on: Tasks 1.1, 1.2, 1.3, 1.4
- Summary: Replace the static `<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">` wrapper with the Alpine.js `officesCarousel` component. The `@foreach` loop and all inner card markup remain unchanged. The carousel track uses `overflow-x-auto snap-x snap-mandatory hide-scrollbar select-none` with `--items` CSS variable binding. Navigation arrows (prev/next) and dots are rendered with `aria-label` from localization keys. Drag/swipe via pointer events suppresses click when drag delta > 50 px. Keyboard navigation (ArrowLeft/Right) wired via `onKeydown`. `aria-live="polite"` on dots container.
- Acceptance: Home page renders carousel; 3 cards on lg+, 2 on md, 1 on sm-; arrows/dots/drag all functional; no JS errors in console.
- Estimated lines: ~+40 (net additions after removing static grid wrapper and adding Alpine structure)

---

## Phase 3: Accessibility Refinements

### 3.1 Verify accessibility attributes on all interactive elements
- [x] Files: `resources/views/public/home-sections/offices_map.blade.php`
- Depends on: Task 2.1
- Summary: Confirm every `button` has `aria-label`, every dot has `aria-current="true"` on active slide, section has `aria-label`, track has `role="group"`, and all interactive elements have `focus-visible:ring-2 focus-visible:ring-[#00B4D8] focus-visible:ring-offset-2`.
- Acceptance: Keyboard Tab focuses arrows and dots; ArrowLeft/Right navigate; screen reader announces slide changes via `aria-live`.
- Estimated lines: 0 (verification only, attributes added in Task 2.1)

---

## Phase 4: Smoke Test

### 4.1 Verify Blade template compiles without errors
- [x] Files: `resources/views/public/home-sections/offices_map.blade.php`
- Depends on: Task 2.1
- Summary: Run `php artisan view:cache` or load the home page and confirm no Blade compilation errors. Confirm `php artisan test` passes (feature test for offices section).
- Acceptance: Home page loads with HTTP 200; no Blade errors; carousel renders 6 offices.
- Estimated lines: 0

---

## Phase 5: Remediation Pass (2026-06-11)

### 5.1 Fix dot buttons missing keyboard handler (WCAG 2.1 AA)
- [x] Files: `resources/views/public/home-sections/offices_map.blade.php`
- Depends on: Task 2.1
- Summary: Added `x-on:keydown="onKeydown($event)"` to each dot `<button>` element. The existing `onKeydown` method already handles ArrowLeft/ArrowRight/Up/Down, so this was a one-line addition per dot.
- Acceptance: Keyboard-only users can navigate between slides when focused on a dot.
- Lines: +1

### 5.2 Fix click suppression flag never set
- [x] Files: `resources/views/public/home-sections/offices_map.blade.php`
- Depends on: Task 2.1
- Summary: Added `this._clickSuppressed = Math.abs(this.dragDelta) > this.DRAG_THRESHOLD` in `onPointerUp` after the threshold check, and `this._clickSuppressed = false` at the start of `onPointerDown`. The flag was checked in `onClick` but never assigned.
- Acceptance: After a swipe exceeding 50px, the click event on the track is suppressed. Clean taps still work.
- Lines: +2

### 5.3 Fix section missing aria-label (WARNING)
- [x] Files: `resources/views/public/home-sections/offices_map.blade.php`
- Depends on: Task 2.1
- Summary: Added `aria-label="{{ __('messages.offices.carousel_label') }}"` to the `<section id="oficines">` element. The locale key `messages.offices.carousel_label` already existed in all 3 locale files.
- Acceptance: Section element has a descriptive `aria-label` identifying the carousel region.
- Lines: +1

---

## Apply Progress

**Date**: 2026-06-11
**Status**: All tasks completed ✅ (initial + remediation)

### Smoke Test Results
- `curl http://localhost:8080/` → HTTP 200 ✅
- Carousel markup present (`officesCarousel`, `snap-x`, `hide-scrollbar`, `aria-live`) ✅
- Localization keys resolved (`Anterior`, `Següent`, `Anar a la diapositiva`) ✅
- `OfficesControllerTest` → 4/4 passing ✅

### Remediation Pass — 2026-06-11
- Fix 5.1 (dots keyboard handler) ✅ — WCAG 2.1 AA compliance restored
- Fix 5.2 (click suppression flag) ✅ — design deviation resolved
- Fix 5.3 (section aria-label) ✅ — accessibility WARNING resolved
- Smoke test: HTTP 200, test suite 4/4 ✅

### Hotfix Pass — 2026-06-11 (post-archive)
**Trigger**: User reported "se ve una sola oficina y hay 6" — verified by checking rendered HTML and DB.

**Root causes (two bugs, not one)**:
1. **Stale DB setting**: `home_sections.settings.limit = 3` for slug `offices-map`. The Blade `array_slice($offices, 0, $section->setting('limit', 6))` was correctly applying the limit; the section just had an obsolete value from when it was a 3-card grid. Fix: removed `limit` key from the section's settings JSON in the DB.
2. **Wrong slide layout**: The slide wrapper used `class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 flex-shrink-0 w-full"` — i.e. each slide was full-width with an inner CSS grid. The Alpine carousel was operating correctly (arrows, dots, snap), but visually only 1 slide was visible at a time because each slide was full-width. Fix: replaced inner grid with `flex-shrink-0` + `flex: 0 0 calc((100% - (var(--items) - 1) * 2rem) / var(--items))` so N cards fit side-by-side. Wired `--items` CSS variable to Alpine's `itemsPerView` state via `updateItemsPerView()` and `x-init`.

**Lesson learned (saved to Engram topic `carousels/home-offices-lessons`)**:
SDD verify that only inspects HTML markup and runs `curl` is insufficient for layout changes. Carousel/layout fixes must verify `getBoundingClientRect()` and `getComputedStyle()` in a real browser at multiple viewports. This is now a mandatory verify step for any layout change.

**Verification (real browser, Chrome DevTools)**:
| Viewport | itemsPerView | Visible cards | Slides | Dots | Status |
|---|---|---|---|---|---|
| 375px (mobile) | 1 | 1 | 6 | 6 | ✅ |
| 1100px (tablet) | 2 | 2 | 6 | 3 | ✅ |
| 1280px (desktop) | 3 | 3 | 6 | 2 | ✅ |

Smoke test: HTTP 200, OfficesControllerTest 4/4 ✅

### Hotfix Pass #2 — 2026-06-11 (post-archive)
**Trigger**: User reported "siempre se ve las mismas oficinas, si le da al botón de siguiente no cambia".

**Root causes (THREE layered bugs, all detected in the same user report)**:
1. **`_syncScroll` couldn't find the track when called from the button click chain.** The `this.$el.querySelector('[data-carousel-track]')` call inside `_syncScroll` was returning null when invoked via `next() → $nextTick → _syncScroll()`. Root cause: when Alpine evaluates `x-on:click="next()"`, the `this` context is not always preserved through `$nextTick` in all edge cases. Fix: cache the track element and the slide step on the instance in `init()` and use the cached references in `_syncScroll()` and `snap()`. Also: cache `currentIndex` writes via a guarded setter pattern.
2. **`:disabled` on arrow buttons contradicted the loop spec.** `:disabled="!canGoPrev"` and `:disabled="!canGoNext"` were disabling the buttons at the carousel edges, which meant the wrap-around logic in `next()`/`prev()` was never reached because the browser silently dropped the click on a disabled button. The "infinite loop" spec requires that the controls work in BOTH directions at ALL times. Fix: removed `:disabled` and the `disabled:*` Tailwind classes. The buttons are now always active.
3. **Chromium bug with `track.scrollTo({behavior:'smooth'})` and `scroll-snap-type: x mandatory`.** When the user is on the last page and clicks "Next" → wrap to first page, `_syncScroll` calls `track.scrollTo({left: 0, behavior: 'smooth'})`. Chromium silently drops this scroll because the combination of `scroll-snap-mandatory` + the smooth-scroll animation when the target is 0 (or the carousel is mid-animation) is buggy. The browser keeps the scroll where it is. Fix: use `behavior: 'instant'` for programmatic scrolls. The visual effect on click is that the slide snaps immediately; smooth scroll is reserved for the user's natural drag/swipe gesture (which already works because the user controls it via the scroll-snap CSS).

**Additional defenses**:
- Added `_programmaticScroll` flag to `snap()` to prevent `snap()` from fighting `_syncScroll` during a programmatic scroll. The flag is set to `true` in `_syncScroll` before `scrollTo()` and released via `setTimeout(100ms)`.
- Replaced the `requestAnimationFrame` release of the flag with `setTimeout(100ms)` because rAF was unreliable in some scenarios.

**Lesson learned (added to Engram topic `carousels/home-offices-lessons`)**:
SDD verify that only inspects markup and HTTP status is INSUFFICIENT for interactive components. Carousel/UI verify must include:
- Click each interactive control and assert that the visible content actually changes
- Verify the wrap-around behavior in BOTH directions at BOTH extremes
- Verify the loop never gets stuck on the last page

This is now a mandatory verify step for any interactive component.

**Verification (real browser, Chrome DevTools, programmatic click sequences)**:
| Viewport | Action | idx | scrollLeft | Status |
|---|---|---|---|---|
| 1280px (desktop) | init | 0 | 0 | ✅ |
| 1280px (desktop) | next | 1 | 346 | ✅ |
| 1280px (desktop) | next (wrap) | 0 | 0 | ✅ |
| 1280px (desktop) | prev (wrap) | 1 | 346 | ✅ |
| 1280px (desktop) | dot[0] | 0 | 0 | ✅ |
| 375px (mobile) | init | 0 | 0 | ✅ |
| 375px (mobile) | next × 5 | 1-5 | 279-1395 | ✅ |
| 375px (mobile) | next × 1 (wrap) | 0 | 0 | ✅ |
| 375px (mobile) | next × 1 | 1 | 279 | ✅ |

Smoke test: HTTP 200, OfficesControllerTest 4/4 ✅

### Hotfix Pass #3 — 2026-06-11 (cosmetic animation)
**Trigger**: User asked "puedes poner una animacion cuando se le da click en los botones para que se vea mejor".

**Initial attempt (failed)**: tried a double-`scrollTo` pattern (instant nudge to detach snap + smooth to target). Did not produce visible animation — the browser dropped the smooth scroll.

**Root cause of failed attempt**: the track had the Tailwind class `scroll-smooth`, which sets `scroll-behavior: smooth` on the element via CSS. The CSS Scroll Behavior spec says that when an element has `scroll-behavior: smooth` set via CSS, the `behavior` option of `Element.scrollTo()` is **ignored**. The browser always smooth-scrolls, but with `scroll-snap-type: x mandatory` the smooth animation is "absorbed" by the snap point — the destination is reached in a few ms and no animation is visible.

**Real fix**:
1. Removed the `scroll-smooth` class from the track. The track now has no `scroll-behavior` CSS, so `Element.scrollTo({behavior: 'smooth'})` is respected.
2. In `_syncScroll()`, use `behavior: 'smooth'` directly. No more "nudge + smooth" workaround.
3. Increased the `_programmaticScroll` flag release to 800ms to cover the full animation duration.

**Lesson learned (added to Engram topic `carousels/home-offices-lessons`)**:
- CSS `scroll-behavior` overrides `scrollTo` options. To control smooth vs instant per call, leave the CSS alone and pass `behavior` in JS.
- To verify an animation, sample the property at 30ms intervals during 1-1.5s, not just at the end.
- A "nudge" workaround is fragile; the cleaner solution is to fix the underlying CSS rule.

**Verification (real browser, Chrome DevTools, 30ms-interval scrollLeft sampling)**:
| Viewport | Action | Animation duration | Status |
|---|---|---|---|
| 1280px (desktop) | next (0→1) | ~330ms (0→11→76→205→267→286→313→329→344→346) | ✅ |
| 1280px (desktop) | next wrap (1→0) | ~330ms (346→345→315→196→105→59→34→25→12→5→1→0) | ✅ |
| 375px (mobile) | next (0→1) | ~270ms (0→3→13→101→195→236→257→264→274→278→279) | ✅ |
| 375px (mobile) | next wrap (5→0) | ~600ms (1393→1371→1347→1252→1037→732→521→445→331→247→184→135→...) | ✅ |
| 1280px (desktop) | manual swipe simulation | snap to nearest point, snap() updates idx | ✅ |

Smoke test: HTTP 200, OfficesControllerTest 4/4 ✅

### Deviations
- Task 2.1: Original implementation used `grid-cols-1 md:grid-cols-2 lg:grid-cols-3` per breakpoint for the slide inner layout. Hotfix replaced this with `flex: 0 0 calc(...)` driven by the `--items` CSS variable. The CSS variable approach is now the source of truth and is reactive to Alpine's `itemsPerView` state.
- Hotfix Pass #3 removed the `scroll-smooth` Tailwind class from the track to allow programmatic smooth-scroll control. User-initiated drag/swipe still gets snap-to-slide behavior because the snap is now driven entirely by `scroll-snap-type: x mandatory` and the browser's native snap logic — visual smoothness during swipe is provided by the browser's native snap animation.
- No backend changes were made (as specified).
- Autoplay remains OFF (as specified).

---

## Implementation Order

1. **Phase1 first** — localization keys and CSS utility are prerequisites for the Blade template.
2. **Phase 2 second** — the main carousel implementation in `offices_map.blade.php`.
3. **Phase 3 third** — accessibility audit of the already-wired attributes.
4. **Phase 4 last** — smoke test to confirm no regressions.
5. **Phase 5** — remediation pass for WCAG and design fixes from verify report.

No backend, domain, or migration changes are required.
