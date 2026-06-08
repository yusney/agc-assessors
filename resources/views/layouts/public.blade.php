<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">

    @php
        // Decode any HTML entities introduced by @section('name', $value) shorthand
        // (Blade's e() pre-escapes the value). Normalising to plain text here lets
        // every output below—<title>, description, og:*, twitter:*—escape exactly once
        // via {{ }}, preventing the &amp;amp; double-encoding that occurred when the
        // partial's {{ }} received an already-escaped string.
        $_seoTitle    = html_entity_decode(trim($__env->yieldContent('seo_title', '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $_seoDesc     = html_entity_decode(trim($__env->yieldContent('seo_description', '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $_seoOgType   = trim($__env->yieldContent('seo_og_type', 'website'));

        // Fallback chain: entity seo_title → global default (PR2) → config('app.name')
        $_seoTitle    = $_seoTitle !== ''
            ? $_seoTitle
            : (isset($globalDefaultTitle) && is_string($globalDefaultTitle) && $globalDefaultTitle !== ''
                ? $globalDefaultTitle
                : config('app.name'));

        // Fallback chain: entity seo_description → global default (PR2) → ''
        $_seoDesc     = $_seoDesc !== ''
            ? $_seoDesc
            : (isset($globalDefaultDescription) && is_string($globalDefaultDescription) && $globalDefaultDescription !== ''
                ? $globalDefaultDescription
                : '');

        $_seoOgType   = $_seoOgType !== '' ? $_seoOgType : 'website';
        $_canonicalUrl = $canonicalUrl ?? url()->current();
    @endphp

    <title>{{ $_seoTitle }}</title>
    <meta name="description" content="{{ $_seoDesc }}">

    @include('public.partials.seo', [
        'canonicalUrl'       => $_canonicalUrl,
        'seoTitle'           => $_seoTitle,
        'seoDescription'     => $_seoDesc,
        'ogLocale'           => $ogLocale ?? 'ca_ES',
        'ogLocaleAlternates' => $ogLocaleAlternates ?? [],
        'ogType'             => $_seoOgType,
        'hreflangAlternates' => $hreflangAlternates ?? [],
    ])

    @if(!empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    {{-- Critical icon CSS: prevents Material Symbols ligature text from flashing before app.css loads. --}}
    <style>
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            line-height: 1;
            display: inline-block;
            white-space: nowrap;
            font-feature-settings: 'liga';
            font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
            overflow: hidden;
            width: 1em;
            height: 1em;
            vertical-align: middle;
        }
    </style>

    {{-- Resource hints --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="https://unpkg.com">
    <link rel="dns-prefetch" href="https://tile.openstreetmap.org">

    {{-- Fonts: Poppins (headings/nav) · Inter (body) · Playfair Display (accents) --}}
    <link rel="preload" as="style"
          href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400;1,600&display=swap">
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400;1,600&display=swap">

    {{-- Material Symbols (preload + stylesheet to start the request early and avoid FOUT) --}}
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    {{-- Font Awesome 6 — only brand icons (social networks) --}}
    <link rel="preload" as="font" type="font/woff2" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/webfonts/fa-brands-400.woff2" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/fontawesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/brands.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('public.components.schema', ['schemas' => $schemas ?? []])

    @stack('head')
</head>
<body class="antialiased bg-white text-[#0f172a]" x-data>

    @include('public.components.navbar')

    <main id="main-content">
        @yield('content')
    </main>

    @include('public.components.trust-bar')

    @include('public.components.footer')
    @include('public.components.cookie-banner')

    @stack('scripts')
</body>
</html>
