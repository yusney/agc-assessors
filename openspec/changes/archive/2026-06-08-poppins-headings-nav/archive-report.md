# Archive Report: poppins-headings-nav

**Change**: poppins-headings-nav
**Archived**: 2026-06-08
**Status**: PASS

---

## Change Summary

Switched headings (h1–h6) and main navigation on the public site from Outfit to Poppins font. Body text (Inter) unchanged. Filament admin excluded.

---

## Commit

```
6bd48ab feat(public): switch headings and navigation to Poppins
```

---

## Files Changed (3 source files)

| File | Impact |
|------|--------|
| `resources/css/app.css` | `--font-headline` token swap + `.nav-link` font-family |
| `resources/views/layouts/public.blade.php` | Google Fonts URL (Outfit → Poppins) + preload hint |
| `resources/views/public/components/navbar.blade.php` | `font-headline` class on 2 mobile nav elements |

---

## Spec Synced

**Full spec created**: `openspec/specs/public-typography/spec.md`

This is a full spec (not a delta) — domain is public site typography covering Poppins for headings and navigation. Copied from `openspec/poppins-headings-nav/spec.md`.

---

## Tasks

- **Total**: 9
- **Complete**: 9
- **Incomplete**: 0

---

## Verification

**Result**: PASS
**Requirements compliant**: 8/8
**Issues found**: 0

---

## Final State

SDD cycle complete. All tasks done, all specs met, build passes, browser karma verified. Ready for next change.