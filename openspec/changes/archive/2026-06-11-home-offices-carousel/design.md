# Design: Home Offices Carousel

## Technical Approach

Replace the static `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3` div in `offices_map.blade.php` with a horizontally-scrolling CSS-scroll-snap carousel driven by a single Alpine.js component. The `$offices` collection passed from the controller is unchanged; only the presentation layer shifts. No backend, domain, or migration changes are required.

---

## Architecture Decisions

### Decision: Native CSS scroll-snap over JavaScript scroll interception

**Choice**: Use `overflow-x-auto snap-x snap-mandatory` on the track + Alpine to manage `currentIndex` and sync dots. Scroll is driven by native browser scrolling; Alpine only reads scroll position on `scroll` events to update dots.

**Alternatives considered**: Full JS-controlled `scrollLeft` manipulation (fragile on mobile, conflicts with momentum scroll). Carousel libs (adds npm dependency).

**Rationale**: Native scroll-snap gives free momentum, hardware acceleration, and touch-scroll handling. Alpine syncs dot indicators and arrow buttons to the scroll position without hijacking the scroll engine.

---

### Decision: Pointer events for drag/swipe (not touch events)

**Choice**: `x-on:pointerdown`, `x-on:pointermove`, `x-on:pointerup` on the track. Pointer events unify mouse and touch into one handler.

**Alternatives considered**: Separate `@touchstart`/`@touchmove`/`@touchend` — requires two code paths.

**Rationale**: Pointer Events API normalises mouse drag and touch swipe into a single event stream. `isDragging` flag prevents click suppression on small movements.

---

### Decision: itemsPerView driven by Alpine `$watch` on window width + debounce

**Choice**: Alpine `init()` registers a `resize` listener with a 100 ms debounce. When width crosses a breakpoint threshold (768 or 1024), `itemsPerView` updates, `currentIndex` clamps to `totalPages - 1`, and the track scrolls to the correct page.

**Alternatives considered**: CSS-only via `@container` queries — not yet baseline-supported. Media-query CSS variables set at init — would not update reactively on resize.

**Rationale**: Alpine `itemsPerView` is the single source of truth for both the CSS grid columns and the slide calculation. CSS grid `grid-cols-1 md:grid-cols-2 lg:grid-cols-3` is applied to each slide div; Alpine sets the `--items` CSS variable on the track to control column count in a single place.

---

## Data Flow

```
Controller (unchanged)
  └── $offices (Collection<Office>) ──► offices_map.blade.php
                                          │
                                          ▼
                               Alpine component (x-data)
                                          │
                    ┌─────────────────────┼─────────────────────┐
                    │                     │                     │
              DOM scroll-snap      dots (aria-current)    arrow buttons
              (native browser)     (sync on scroll)       (sync on scroll)
```

---

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `resources/views/public/home-sections/offices_map.blade.php` | Modify | Replace static grid div with Alpine carousel; preserve all card markup and Leaflet map |
| `resources/lang/ca/messages.php` | Modify | Add `offices.carousel_prev`, `offices.carousel_next`, `offices.carousel_go_to`, `offices.carousel_drag_hint` |
| `resources/lang/es/messages.php` | Modify | Same carousel keys |
| `resources/lang/en/messages.php` | Modify | Same carousel keys |
| `resources/css/app.css` | Modify | Add `.hide-scrollbar` utility in `@layer utilities` |

---

## Alpine.js Component

### Component name
`officesCarousel`

### State
```javascript
{
    currentIndex: 0,       // 0-based logical slide index
    itemsPerView: 3,       // reactive, updated on resize (3=lg, 2=md, 1=sm-)
    isDragging: false,
    startX: 0,             // pointer x at drag start
    currentX: 0,           // pointer x during drag
    dragDelta: 0,          // currentX - startX
    DRAG_THRESHOLD: 50,    // px before drag-suppresses-click
    itemCount: 6,          // total offices (set by Blade)
}
```

### Computed
```javascript
{
    totalPages: () => Math.ceil(this.itemCount / this.itemsPerView),
    canGoPrev: () => this.currentIndex > 0,
    canGoNext: () => this.currentIndex < this.totalPages - 1,
    // CSS grid cols driven by itemsPerView via style="--items: {n}"
}
```

### Methods
```
init()             — register resize listener (debounced 100ms), set itemsPerView from initial width
onPointerDown(e)   — set isDragging=true, startX=e.clientX, currentX=e.clientX
onPointerMove(e)   — if !isDragging return; currentX=e.clientX; dragDelta=currentX-startX
onPointerUp(e)     — if dragDelta > DRAG_THRESHOLD: e.preventDefault() suppresses click; 
                     else determine direction from dragDelta and navigate accordingly.
                     isDragging=false; dragDelta=0
onClick(e)         — if isDragging && |dragDelta| > DRAG_THRESHOLD: e.preventDefault(); e.stopPropagation()
next()             — currentIndex = (currentIndex < totalPages-1) ? currentIndex+1 : 0  // wraps
prev()             — currentIndex = (currentIndex > 0) ? currentIndex-1 : totalPages-1  // wraps
goTo(n)            — currentIndex = n; scroll track to correct snap point
snap()             — reads scrollLeft, computes currentIndex = Math.round(scrollLeft / slideWidth)
onKeydown(e)       — if ArrowRight/ArrowDown: next(); e.preventDefault()
                     if ArrowLeft/ArrowUp: prev(); e.preventDefault()
_syncScroll()      — called on Alpine effect; scrolls track to align with currentIndex
```

### Responsive itemsPerView (resize handler)
```javascript
// In init():
window.addEventListener('resize', this._debounce(() => {
    const w = window.innerWidth;
    const next = w >= 1024 ? 3 : w >= 768 ? 2 : 1;
    if (next !== this.itemsPerView) {
        this.itemsPerView = next;
        if (this.currentIndex >= this.totalPages) this.currentIndex = this.totalPages - 1;
        this.$nextTick(() => this._syncScroll());
    }
}, 100));
```

---

## Tailwind 4 Classes

### Outer container
```html
<div x-data="officesCarousel" class="relative w-full" ...>
```

### Track (scroll snap container)
```html
<div class="flex overflow-x-auto snap-x snap-mandatory hide-scrollbar select-none gap-8"
     style="--items: v-bind(itemsPerView);"
     x-on:scroll.window.passive="snap()"
     x-on:pointerdown="onPointerDown($event)"
     x-on:pointermove="onPointerMove($event)"
     x-on:pointerup="onPointerUp($event)"
     x-on:click="onClick($event)"
     role="group"
     aria-label="{{ __('messages.offices.carousel_label') }}">
```
**Per-breakpoint slide width**: Each slide is a grid with `grid-cols-[repeat(var(--items),1fr)]`; Alpine sets `--items` via `style` binding. Fallback per-breakpoint grid cols (set on the slide div):
```html
class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 flex-shrink-0 w-full gap-8 snap-start"
```

### Slide (card wrapper)
Each `@foreach` card is wrapped in a `<div>` with `snap-start` on the slide div. Cards themselves keep ALL existing classes (see Visual Parity below).

### Arrows
```html
<button type="button"
        x-on:click="prev()"
        x-on:keydown="onKeydown($event)"
        :disabled="!canGoPrev"
        aria-label="{{ __('messages.offices.carousel_prev') }}"
        class="... focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00B4D8] focus-visible:ring-offset-2 ...">
```
Same pattern for `next()`.

### Dots
```html
<template x-for="n in totalPages" :key="n">
  <button type="button"
          x-on:click="goTo(n - 1)"
          :aria-label="`{{ __('messages.offices.carousel_go_to') }}`.replace(':slide', n)"
          :aria-current="currentIndex === n - 1 ? 'true' : 'false'"
          class="... focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00B4D8] focus-visible:ring-offset-2 ...">
    <span class="sr-only" x-text="`{{ __('messages.offices.carousel_go_to') }}`.replace(':slide', n)"></span>
    <!-- visual dot indicator -->
  </button>
</template>
```

### hide-scrollbar utility (app.css)
```css
@layer utilities {
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
}
```

---

## Localization Keys

Added under `offices` in all three `messages.php` files:

```php
'offices' => [
    // ... existing keys ...
    'carousel_prev'      => 'Anterior',
    'carousel_next'      => 'Següent',
    'carousel_go_to'     => 'Anar a la diapositiva :slide',
    'carousel_drag_hint' => 'Arrossega per navegar',
    'carousel_label'     => 'Carrusel d\'oficines',
],
```

(es) `'carousel_prev' => 'Anterior'`, `'carousel_next' => 'Siguiente'`, `'carousel_go_to' => 'Ir a la diapositiva :slide'`, `'carousel_drag_hint' => 'Desliza para navegar'`, `'carousel_label' => 'Carrusel de oficinas'`

(en) `'carousel_prev' => 'Previous'`, `'carousel_next' => 'Next'`, `'carousel_go_to' => 'Go to slide :slide'`, `'carousel_drag_hint' => 'Drag to navigate'`, `'carousel_label' => 'Offices carousel'`

---

## Accessibility

| Element | ARIA attribute | Reason |
|---------|---------------|--------|
| Section `<section>` | `aria-label="{{ __('messages.offices.carousel_label') }}"` | Identifies the carousel region |
| Track div | `role="group"`, `aria-label="..."` | Groups carousel slides |
| Each slide card | `role="group"`, `aria-label="Office: {name}"` | Labels each slide |
| Prev/Next buttons | `aria-label="{{ __('messages.offices.carousel_prev/next') }}"` | Accessible name |
| Dots | `aria-label="{{ __('messages.offices.carousel_go_to') }}"`, `aria-current="true"` on active | Indicates current position |
| Keyboard | `x-on:keydown="onKeydown($event)"` — ArrowLeft/ArrowRight triggers prev/next | WCAG 2.1 AA |
| Focus rings | `focus-visible:ring-2 focus-visible:ring-[#00B4D8] focus-visible:ring-offset-2` on all interactive elements | Visible focus indicator |
| Live region | `aria-live="polite"` on dots container | Announces slide changes to screen readers |

---

## Visual Parity — Card Markup Preserved Exactly

The `@foreach` loop and inner card markup remain unchanged. Only the outer `<div class="grid ...>` is replaced with the carousel track. The card classes that MUST be preserved:

```html
<div class="bg-white rounded-xl shadow-sm border border-[#E2E8F0] border-l-4 border-l-[#00346f]
            hover:shadow-md transition-shadow duration-300 p-6 flex flex-col">
```

Typography preserved:
- Name: `font-headline text-[18px] font-semibold text-[#1E293B]`
- Icon: `material-symbols-outlined text-[#00B4D8] text-[18px]`
- Address: `text-[14px] text-[#64748B]`
- Phone/email links: `text-[14px] text-[#424751] hover:text-[#00346f] transition-colors`
- CTA: `text-[14px] text-[#00346f] font-semibold hover:text-[#00B4D8]`

Border-left accent: `border-l-4 border-l-[#00346f]` (primary `#00346f`).
Accent icon color: `#00B4D8` (accent cyan).

---

## Drag/Swipe — Click Suppression Logic

```
pointerdown → isDragging=true, startX=clientX
pointermove → if !isDragging return; dragDelta = clientX - startX
pointerup   → if |dragDelta| > 50px:
                isClickSuppressed = true  (stored in component state)
              else:
                navigate by direction (dragDelta < 0 ? next : prev)
              isDragging=false; dragDelta=0

click event on track → if isClickSuppressed:
                          e.preventDefault(); e.stopPropagation()
                          isClickSuppressed = false
```

This ensures tapping a card's CTA "Ver oficina" link works without navigating the carousel, while swiping/dragging still changes slides.

---

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Feature (PHPUnit) | `$offices` passed to view renders without error; carousel Blade partial compiles | `Blade::render()` in a test; no DOM testing |
| Manual / browser | Carousel renders 3/2/1 cards per breakpoint; arrows navigate; dots reflect index; drag/swipe works on mobile; keyboard ArrowLeft/Right navigates; card CTA links still work | Dev validation checklist |
| Visual regression | No regression in card typography, colors, spacing | Compare before/after screenshot at lg/md/sm breakpoints |

No Dusk/Playwright in `composer.json` — E2E test automation is out of scope for this change. The PHP Feature test ensures the Blade template compiles cleanly with the new Alpine syntax.

---

## Migration / Rollback

**No migration required.** Purely a presentation-layer change.

**Rollback**: `git checkout HEAD^ -- resources/views/public/home-sections/offices_map.blade.php` reverts the carousel to the static grid. The `array_slice` limit on `$offices` is already in git history and can be restored trivially.

---

## Open Questions

- [ ] None — all decisions resolved in proposal phase (loop=wrap, arrows+dots=both, no autoplay, card body not clickable, natural order).

---

## Risks and Mitigations

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Leaflet map intercepts horizontal scroll on mobile | Low | `snap-x` is native; Leaflet has `scrollWheelZoom: false`. Test on iOS Safari and Android Chrome. |
| Resize observer causes layout shift on load | Low | Alpine `init()` runs after DOM is ready; `$nextTick` before `_syncScroll()` prevents flash. |
| Dots count out of sync with scroll position after rapid resize | Low | `snap()` recomputes `currentIndex` on every scroll event; `totalPages` is a computed getter. |
| Click suppression prevents tapping CTA on slow drag releases | Low | Threshold is 50 px; a real drag will exceed this; a tap will not. |