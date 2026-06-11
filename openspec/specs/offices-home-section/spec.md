# offices-home-section

## Purpose

The `offices-home-section` blade partial (`offices_map.blade.php`) renders the offices carousel on the public home page. It displays all active offices in a horizontally-scrolling Alpine.js carousel that shows 3 cards on desktop, 2 on tablet, and 1 on mobile, with manual navigation (arrows, dots, drag/swipe). The `$offices` data contract is unchanged; only the presentation layer shifts from a static CSS grid to a carousel. No backend, domain, or migration changes are required.

---

## MODIFIED Requirements

### Requirement: Carousel — Viewport-Based Card Display

The carousel MUST display exactly 3 cards simultaneously on `lg+` viewports, exactly 2 cards on `md` viewports, and exactly 1 card on `sm-` viewports. Each "slide" is a CSS grid row whose column count matches the viewport breakpoint. The carousel track MUST use native CSS scroll-snap (`snap-x`) for browser-managed alignment; no JavaScript scroll interception is REQUIRED.

#### Scenario: Desktop displays 3 cards

- GIVEN a browser viewport of width 1024px or greater
- WHEN the home page renders the offices section
- THEN the carousel track shows exactly 3 office cards side-by-side
- AND the carousel has `Math.ceil(6 / 3) = 2` logical slides

#### Scenario: Tablet displays 2 cards

- GIVEN a browser viewport of width 768px to 1023px
- WHEN the home page renders the offices section
- THEN the carousel track shows exactly 2 office cards side-by-side
- AND the carousel has `Math.ceil(6 / 2) = 3` logical slides

#### Scenario: Mobile displays 1 card

- GIVEN a browser viewport of width less than 768px
- WHEN the home page renders the offices section
- THEN the carousel track shows exactly 1 office card
- AND the carousel has `Math.ceil(6 / 1) = 6` logical slides

---

### Requirement: Navigation Controls — Arrows, Dots, and Drag/Swipe

The carousel MUST provide three navigation mechanisms: prev/next arrow buttons, dot indicators, and touch drag/swipe. Autoplay MUST be OFF by default. Loop (infinite wrap-around) MUST be enabled.

#### Scenario: Prev/Next arrows navigate slides

- GIVEN the carousel is on slide N (1 ≤ N ≤ totalSlides)
- WHEN the user clicks the "Next" arrow
- THEN the carousel scrolls to slide N+1; if N equals the last slide, it wraps to slide 1
- AND the "Previous" arrow scrolls to slide N−1; if N equals the first slide, it wraps to the last slide

#### Scenario: Dot indicators reflect current slide and allow jump navigation

- GIVEN the carousel has N logical slides
- WHEN the carousel renders
- THEN N dot indicators are displayed, each representing a slide
- AND the dot corresponding to the currently visible slide is visually marked as active
- AND clicking dot M scrolls the carousel to slide M

#### Scenario: Touch drag/swipe navigates on mobile

- GIVEN the user is on a touch device viewing the carousel
- WHEN the user swipes left (or right) on the carousel track
- THEN the carousel scrolls to the next (or previous) slide respectively
- AND the active dot updates to reflect the new slide

#### Scenario: Loop wraps from last slide to first

- GIVEN the carousel is displaying the last logical slide
- WHEN the user clicks "Next"
- THEN the carousel wraps and displays the first slide
- AND the active dot updates to the first dot

---

### Requirement: Keyboard Accessibility

The carousel navigation controls MUST be fully operable via keyboard. The component MUST meet WCAG 2.1 AA standards for focus management.

#### Scenario: Arrow key navigation

- GIVEN the carousel navigation has keyboard focus
- WHEN the user presses the Tab key to focus an arrow button or dot
- THEN the focused control has a visible focus indicator
- AND pressing the Right Arrow or Down Arrow key triggers "Next"
- AND pressing the Left Arrow or Up Arrow key triggers "Previous"

#### Scenario: aria-labels on navigation controls

- GIVEN the carousel renders navigation arrows and dots
- THEN each arrow button MUST have an `aria-label` referencing a localized string (e.g., `messages.offices.carousel_prev`, `messages.offices.carousel_next`)
- AND each dot MUST have an `aria-label` referencing a localized string (e.g., `messages.offices.carousel_go_to`, interpolated with the slide number)

---

### Requirement: Visual Parity with Static Cards

The carousel cards MUST be visually identical to the existing static grid cards in typography, color tokens, spacing, border accents, and hover states. The card border-left accent color MUST be `#00346f`. The card hover shadow transition MUST be preserved.

#### Scenario: Card visual elements match static grid

- GIVEN a rendered carousel card and a static grid card
- THEN both display: `font-headline` name at 18px semibold, `location_on` icon in `#00B4D8`, address/city in `#64748B`, phone/email links in `#424751` with `#00346f` hover, and border-left accent in `#00346f`
- AND both have `rounded-xl`, `shadow-sm`, `border border-[#E2E8F0]`, `border-l-4 border-l-[#00346f]`, and `hover:shadow-md transition-shadow duration-300`

---

### Requirement: CTA Link Behavior — Card Body Not Clickable

The office card body MUST NOT be a full-card clickable link. Only the existing "Ver oficina" CTA link inside the card MUST be the navigable element. This preserves SEO link equity and analytics tracking.

#### Scenario: Only CTA link is clickable inside the card

- GIVEN a rendered office card in the carousel
- WHEN the user hovers over the card body (outside the CTA)
- THEN the cursor remains the default pointer (not a hand)
- AND clicking the card body does NOT navigate to the office page
- AND only clicking the "Ver oficina" link navigates to the office page

---

### Requirement: Sort Order

The carousel MUST display offices in the order determined by the existing `sort` or `position` field if one exists on the Office entity. If no such field exists, the carousel MUST display offices in the natural order returned by the repository.

#### Scenario: Offices displayed in defined sort order

- GIVEN the `$offices` collection passed to the view is sorted by a `position` field (or by `is_active` + `id` as default)
- WHEN the carousel renders
- THEN the cards appear in that same order, with no re-sorting in the view layer

---

### Requirement: Multi-Language Navigation Strings

All user-facing strings rendered by the carousel component (e.g., `aria-label` values for navigation controls) MUST use Laravel localization keys from `resources/lang/{ca,es,en}/messages.php`. No string literals MUST be hardcoded in the Blade template.

#### Scenario: Navigation strings are localized

- GIVEN the application locale is set to `ca`, `es`, or `en`
- WHEN the carousel renders navigation arrows
- THEN each arrow button's `aria-label` resolves to the corresponding `messages.offices.carousel_prev` or `messages.offices.carousel_next` translation
- AND dot `aria-label` values resolve to `messages.offices.carousel_go_to` with the slide number interpolated

---

### Requirement: Autoplay — Disabled by Default

Autoplay MUST be OFF by default. The carousel state MUST expose an `autoplay` boolean property (defaulting to `false`) that can be toggled via Alpine state if needed in the future, but no UI toggle is required for this change.

#### Scenario: No autoplay on page load

- GIVEN the home page has loaded
- WHEN the page finishes rendering
- THEN the carousel does NOT auto-advance slides
- AND the user MUST interact with arrows, dots, or swipe to navigate
