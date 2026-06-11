# Archive Report: home-offices-carousel

**Change**: Home Offices Carousel — replace static grid with Alpine.js carousel in `offices_map.blade.php`
**Archived**:2026-06-11
**Final commit**: `3e99529` — `docs(seo): add SEO guide and offices plan; openspec: add sdd-init and editor specs`
**Mode**: openspec
**Sign-off**: `PASS` (0 CRITICAL, 0 WARNING, 0 SUGGESTION; 15/15 spec scenarios compliant)

---

## What Was Built

The `offices_map.blade.php` blade partial was converted from a static CSS3-column grid to a horizontally-scrolling Alpine.js carousel. The carousel shows3 cards on desktop (`lg+`), 2 on tablet (`md`), and 1 on mobile (`sm-`), with prev/next arrow buttons, dot indicators, and touch drag/swipe navigation. All6 active offices are displayed (the previous `array_slice` limit was removed). The card markup, typography, colors, and hover states were preserved exactly. Localization keys for carousel accessibility strings were added to all three locales (`ca`, `es`, `en`). A `hide-scrollbar` CSS utility was added to `app.css`.

A remediation pass (Phase 5) fixed three issues found during initial verification:
- Dot buttons missing keyboard handler (WCAG 2.1 AA)
- Click suppression flag never set (design deviation)
- Section element missing `aria-label` (accessibility warning)

---

## Diff Stats

| File | Change |
|------|--------|
| `resources/views/public/home-sections/offices_map.blade.php` | ~+357 lines (rewritten as carousel) |
| `resources/lang/ca/messages.php` | +5 lines (carousel localization keys) |
| `resources/lang/es/messages.php` | +5 lines (carousel localization keys) |
| `resources/lang/en/messages.php` | +5 lines (carousel localization keys) |
| `resources/css/app.css` | +8 lines (`hide-scrollbar` utility) |

Total: ~+380 net lines across5 files.

---

## Sign-off Result

**Verdict**: `PASS` — 0 CRITICAL, 0 WARNING, 0 SUGGESTION

After a remediation pass (Phase 5,2026-06-11), all 15 spec scenarios were confirmed compliant. All smoke tests passed (HTTP 200, 4/4 PHPUnit tests, locale key resolution in all 3 locales).

---

## Open Product Questions (Unanswered)

The following product decisions were explicitly deferred during the proposal phase and remain unresolved:

| # | Question | Default (from proposal) | Status |
|---|----------|--------------------------|--------|
| 1 | Autoplay interval / enable | No autoplay | Resolved — OFF by default in implementation |
| 2 | Loop (infinite wrap) vs stop at edges | Stop at edges | Resolved — loop enabled (wrap-around) in implementation |
| 3 | Dots vs arrows vs both | Both | Resolved — both implemented |
| 4 | Full-card clickable vs CTA only | CTA only | Resolved — CTA only in implementation |
| 5 | Manual sort order vs natural order | Natural order | Resolved — natural order in implementation |
| 6 | Mouse drag support (vs touch only) | Pointer events (mouse+touch) | Partially resolved — touch drag only; mouse drag not implemented (spec only required touch) |

---

## Archive Contents

- `proposal.md` ✅
- `specs/offices-home-section/spec.md` ✅ (delta; canonical merged to `openspec/specs/offices-home-section/spec.md`)
- `design.md` ✅
- `tasks.md` ✅ (all 11 tasks checked `[x]`)
- `verify-report.md` ✅ (original + re-verification after remediation)

---

## Next Steps for User

```bash
# 1. Review the canonical spec
cat openspec/specs/offices-home-section/spec.md

# 2. Review the archived change
ls openspec/changes/archive/2026-06-11-home-offices-carousel/

# 3. Commit and push (if desired)
git add -A && git commit -m "feat(home): replace offices grid with Alpine.js carousel" && git push
```
