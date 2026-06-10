#!/usr/bin/env python3
"""
Local SEO Audit Script
Simulates Google Rich Results Test for the AGC Assessors /oficinas pages.
Extracts JSON-LD and validates it against the schema.org LocalBusiness spec.
"""
import urllib.request
import re
import json
import sys
from typing import Any

BASE = "http://localhost:8080"

# spec: properties Google requires/recommends for LocalBusiness
REQUIRED_LOCALBUSINESS = {
    "@context": str,
    "@type": str,
    "name": str,
    "address": dict,   # nested PostalAddress
    "telephone": str,  # recommended
}

RECOMMENDED_LOCALBUSINESS = {
    "url": str,
    "geo": dict,
    "openingHoursSpecification": list,
    "areaServed": list,
    "image": str,
    "priceRange": str,
    "email": str,
    "parentOrganization": dict,
}

POSTAL_ADDRESS = {
    "streetAddress": str,
    "addressLocality": str,
    "addressCountry": str,
}

GEO = {
    "latitude": (int, float),
    "longitude": (int, float),
}

OPENING_HOURS = {
    "dayOfWeek": str,
    "opens": str,
    "closes": str,
}


def fetch(path: str) -> str:
    with urllib.request.urlopen(f"{BASE}{path}", timeout=10) as r:
        return r.read().decode("utf-8")


def extract_jsonld(html: str) -> list[dict]:
    scripts = re.findall(
        r'<script type="application/ld\+json">\s*(.*?)\s*</script>', html, re.DOTALL
    )
    result = []
    for s in scripts:
        try:
            d = json.loads(s)
            if isinstance(d, list):
                result.extend(d)
            else:
                result.append(d)
        except json.JSONDecodeError as e:
            print(f"  ⚠ JSON-LD parse error: {e}")
    return result


def typecheck(obj: Any, expected: type | tuple, path: str, errors: list) -> bool:
    if not isinstance(obj, expected):
        errors.append(f"  ✗ {path}: expected {expected}, got {type(obj).__name__}")
        return False
    return True


def validate_postal_address(addr: dict, path: str, errors: list, warnings: list) -> None:
    for field, t in POSTAL_ADDRESS.items():
        if field not in addr:
            errors.append(f"  ✗ {path}.{field}: missing required field")
        elif not typecheck(addr[field], t, f"{path}.{field}", errors):
            pass
    if "addressRegion" in addr:
        if not typecheck(addr["addressRegion"], str, f"{path}.addressRegion", errors):
            pass
    if "postalCode" in addr:
        if not typecheck(addr["postalCode"], str, f"{path}.postalCode", errors):
            pass


def validate_geo(geo: dict, path: str, errors: list) -> None:
    for field, t in GEO.items():
        if field not in geo:
            errors.append(f"  ✗ {path}.{field}: missing")
        elif not typecheck(geo[field], t, f"{path}.{field}", errors):
            pass


def validate_opening_hours(specs: list, path: str, errors: list) -> None:
    valid_days = {"Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"}
    time_re = re.compile(r"^\d{2}:\d{2}$")
    for i, spec in enumerate(specs):
        sp = f"{path}[{i}]"
        for field, t in OPENING_HOURS.items():
            if field not in spec:
                errors.append(f"  ✗ {sp}.{field}: missing")
            elif not typecheck(spec[field], t, f"{sp}.{field}", errors):
                pass
        if "dayOfWeek" in spec and spec["dayOfWeek"] not in valid_days:
            errors.append(f"  ✗ {sp}.dayOfWeek: '{spec['dayOfWeek']}' is not a valid day name")
        if "opens" in spec and not time_re.match(spec["opens"]):
            errors.append(f"  ✗ {sp}.opens: '{spec['opens']}' must be HH:MM")
        if "closes" in spec and not time_re.match(spec["closes"]):
            errors.append(f"  ✗ {sp}.closes: '{spec['closes']}' must be HH:MM")


def validate_area_served(areas: list, path: str, errors: list) -> None:
    for i, area in enumerate(areas):
        ap = f"{path}[{i}]"
        if "name" not in area:
            errors.append(f"  ✗ {ap}.name: missing")
        elif not typecheck(area["name"], str, f"{ap}.name", errors):
            pass


def validate_localbusiness(lb: dict) -> tuple[list, list]:
    errors, warnings = [], []
    p = f"LocalBusiness '{lb.get('name', '?')}'"

    for field, t in REQUIRED_LOCALBUSINESS.items():
        if field not in lb:
            errors.append(f"  ✗ {p}.{field}: missing REQUIRED field")
        elif not typecheck(lb[field], t, f"{p}.{field}", errors):
            pass

    for field, t in RECOMMENDED_LOCALBUSINESS.items():
        if field not in lb:
            warnings.append(f"  ⚠ {p}.{field}: missing recommended field")
        elif not typecheck(lb[field], t, f"{p}.{field}", errors):
            pass

    if "@type" in lb and lb["@type"] not in ("LocalBusiness",):
        if "LocalBusiness" not in (lb["@type"] if isinstance(lb["@type"], list) else [lb["@type"]]):
            errors.append(f"  ✗ {p}.@type: must be 'LocalBusiness' or include it")

    if "address" in lb and isinstance(lb["address"], dict):
        validate_postal_address(lb["address"], f"{p}.address", errors, warnings)

    if "geo" in lb and isinstance(lb["geo"], dict):
        validate_geo(lb["geo"], f"{p}.geo", errors)

    if "openingHoursSpecification" in lb:
        validate_opening_hours(lb["openingHoursSpecification"], f"{p}.openingHoursSpecification", errors)

    if "areaServed" in lb and isinstance(lb["areaServed"], list):
        validate_area_served(lb["areaServed"], f"{p}.areaServed", errors)

    return errors, warnings


def validate_itemlist(il: dict) -> tuple[list, list]:
    errors, warnings = [], []
    p = "ItemList"
    if "itemListElement" not in il:
        errors.append(f"  ✗ {p}.itemListElement: missing")
        return errors, warnings
    items = il["itemListElement"]
    if not isinstance(items, list):
        errors.append(f"  ✗ {p}.itemListElement: must be array")
        return errors, warnings
    for i, item in enumerate(items):
        if "position" not in item:
            errors.append(f"  ✗ {p}[{i}].position: missing")
        if "url" not in item:
            errors.append(f"  ✗ {p}[{i}].url: missing")
        if "name" not in item:
            warnings.append(f"  ⚠ {p}[{i}].name: missing (recommended)")
    return errors, warnings


def audit_page(path: str) -> dict:
    html = fetch(path)
    schemas = extract_jsonld(html)

    # Title and description
    title_m = re.search(r"<title>(.*?)</title>", html)
    title = title_m.group(1) if title_m else None
    desc_m = re.search(r'<meta name="description" content="(.*?)"', html)
    description = desc_m.group(1) if desc_m else None
    canonical_m = re.search(r'<link rel="canonical" href="(.*?)"', html)
    canonical = canonical_m.group(1) if canonical_m else None
    hreflangs = re.findall(r'<link rel="alternate"[^>]+hreflang="([^"]+)"[^>]+href="([^"]+)"', html)

    # Detect LocalBusiness
    localbusinesses = [s for s in schemas if s.get("@type") == "LocalBusiness"]
    itemlists = [s for s in schemas if s.get("@type") == "ItemList"]

    return {
        "path": path,
        "title": title,
        "title_len": len(title) if title else 0,
        "description": description,
        "description_len": len(description) if description else 0,
        "canonical": canonical,
        "hreflangs": [{"lang": l, "url": u} for l, u in hreflangs],
        "localbusinesses": localbusinesses,
        "itemlists": itemlists,
        "schema_count": len(schemas),
    }


def main():
    pages = [
        ("/es/oficinas", "Hub (es)"),
        ("/es/oficinas/caldes-de-montbui", "Caldes (es)"),
        ("/es/oficinas/sant-celoni", "Sant Celoni (es)"),
        ("/es/oficinas/mollet-del-valles", "Mollet (es)"),
        ("/es/oficinas/granollers", "Granollers (es)"),
        ("/es/oficinas/prats-de-llucanes", "Prats (es)"),
        ("/es/oficinas/manlleu", "Manlleu (es)"),
        ("/oficines", "Hub (ca)"),
        ("/oficines/granollers", "Granollers (ca)"),
        ("/en/offices", "Hub (en)"),
        ("/en/offices/granollers", "Granollers (en)"),
    ]

    grand_errors = 0
    grand_warnings = 0

    print("=" * 78)
    print(" AGC ASSESSORS — LOCAL SEO AUDIT (LocalBusiness + ItemList)")
    print("=" * 78)

    for path, label in pages:
        print(f"\n{'─' * 78}")
        print(f"📄 {label}  →  {path}")
        print(f"{'─' * 78}")

        result = audit_page(path)

        # Title / description
        if result["title"]:
            t_status = "✓" if 30 <= result["title_len"] <= 60 else "✗"
            print(f"  {t_status} Title ({result['title_len']} chars): {result['title']}")
        else:
            print("  ✗ Title: MISSING")

        if result["description"]:
            d_status = "✓" if 100 <= result["description_len"] <= 160 else "✗"
            print(f"  {d_status} Description ({result['description_len']} chars)")
        else:
            print("  ✗ Description: MISSING")

        if result["canonical"]:
            print(f"  ✓ Canonical: {result['canonical']}")
        else:
            print("  ✗ Canonical: MISSING")

        hreflang_status = "✓" if len(result["hreflangs"]) == 4 else f"✗ ({len(result['hreflangs'])})"
        print(f"  {hreflang_status} Hreflang alternates: {len(result['hreflangs'])} (ca/es/en/x-default expected)")

        # LocalBusiness validation
        page_errors = 0
        page_warnings = 0
        if result["localbusinesses"]:
            print(f"\n  📍 LocalBusiness schema ({len(result['localbusinesses'])} found):")
            for lb in result["localbusinesses"]:
                errs, warns = validate_localbusiness(lb)
                page_errors += len(errs)
                page_warnings += len(warns)
                if not errs:
                    print(f"    ✓ {lb.get('name', '?')}: passes validation")
                for e in errs:
                    print(f"    {e}")
                for w in warns:
                    print(f"    {w}")
                # Detail dump
                addr = lb.get("address", {})
                geo = lb.get("geo", {})
                oh = lb.get("openingHoursSpecification", [])
                areas = lb.get("areaServed", [])
                print(f"      • address: {addr.get('streetAddress', '?')}, {addr.get('addressLocality', '?')}")
                print(f"      • geo: {geo.get('latitude', '?')}, {geo.get('longitude', '?')}")
                print(f"      • phone: {lb.get('telephone', '?')}")
                print(f"      • openingHoursSpecification: {len(oh)} day rules")
                if areas:
                    names = [a.get("name", "?") for a in areas[:5]]
                    extra = f" +{len(areas)-5} more" if len(areas) > 5 else ""
                    print(f"      • areaServed: {len(areas)} cities → {', '.join(names)}{extra}")
        else:
            # Hub pages: identify by no slug after the locale
            # e.g. /es/oficinas → no slug; /es/oficinas/caldes-de-montbui → has slug
            path_after_office = path.split("/oficinas/")[-1].split("/oficines/")[-1].split("/offices/")[-1]
            is_individual = path_after_office not in ("", path)
            if is_individual:
                print("\n  ✗ LocalBusiness schema: MISSING (REQUIRED for individual page)")
                page_errors += 1
            else:
                print("\n  ⊘ No LocalBusiness (hub page, correct — each office has its own URL)")

        # ItemList validation (hub pages only)
        if result["itemlists"]:
            print(f"\n  📋 ItemList schema ({len(result['itemlists'])} found):")
            for il in result["itemlists"]:
                errs, warns = validate_itemlist(il)
                page_errors += len(errs)
                page_warnings += len(warns)
                items = il.get("itemListElement", [])
                if not errs:
                    print(f"    ✓ ItemList: {len(items)} items, passes validation")
                for e in errs:
                    print(f"    {e}")
                for w in warns:
                    print(f"    {w}")
        elif path.endswith("oficinas") or path.endswith("oficines") or path.endswith("offices"):
            print("\n  ✗ ItemList schema: MISSING (hub page should have one)")
            page_errors += 1

        grand_errors += page_errors
        grand_warnings += page_warnings
        print(f"\n  Result: {page_errors} errors, {page_warnings} warnings")

    print(f"\n{'=' * 78}")
    if grand_errors == 0 and grand_warnings == 0:
        print(f" ✅ ALL {len(pages)} PAGES PASS LOCAL SEO VALIDATION")
    else:
        print(f" ❌ {grand_errors} ERRORS, {grand_warnings} WARNINGS across {len(pages)} pages")
    print(f"{'=' * 78}")

    return 0 if grand_errors == 0 else 1


if __name__ == "__main__":
    sys.exit(main())
