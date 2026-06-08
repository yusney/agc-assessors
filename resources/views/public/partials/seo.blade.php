{{--
    SEO Head Partial — public/partials/seo.blade.php

    Renders the complete multilingual SEO meta block into <head>.

    Expected variables (injected by layouts/public.blade.php via SeoComposer):
      $canonicalUrl        string  — absolute URL for the current page in the active locale
      $seoTitle            string  — resolved page title (section fallback applied in layout)
      $seoDescription      string  — resolved meta description
      $ogLocale            string  — og:locale code for the active locale (e.g. 'ca_ES')
      $ogLocaleAlternates  array   — regional codes for non-active locales (['es_ES', 'en_GB'])
      $ogType              string  — og:type (default: 'website')
      $hreflangAlternates  array   — [['locale' => 'ca', 'url' => '...'], ...]

    NOTE: No <meta name="keywords"> tag is emitted. Keywords are out of scope
    per design decision D4 (spec non-goal, dead code in SEOData VO).
--}}

{{-- ── Canonical ─────────────────────────────────────────────────────────── --}}
<link rel="canonical" href="{{ $canonicalUrl }}">

{{-- ── hreflang alternates (ca / es / en + x-default) ─────────────────── --}}
@foreach ($hreflangAlternates as $alt)
<link rel="alternate" hreflang="{{ $alt['locale'] }}" href="{{ $alt['url'] }}">
@endforeach
{{-- x-default points to the Catalan (unprefixed) URL --}}
@php
    $xDefaultUrl = collect($hreflangAlternates)->firstWhere('locale', 'ca')['url'] ?? $canonicalUrl;
@endphp
<link rel="alternate" hreflang="x-default" href="{{ $xDefaultUrl }}">

{{-- ── Open Graph ───────────────────────────────────────────────────────── --}}
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:type" content="{{ $ogType ?? 'website' }}">
<meta property="og:locale" content="{{ $ogLocale }}">
@foreach ($ogLocaleAlternates as $alternate)
<meta property="og:locale:alternate" content="{{ $alternate }}">
@endforeach

{{-- ── Twitter Card ─────────────────────────────────────────────────────── --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
