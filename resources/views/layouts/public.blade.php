<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">

    <title>@yield('seo_title', config('app.name'))</title>
    <meta name="description" content="@yield('seo_description', '')">
    @hasSection('seo_canonical')
        <link rel="canonical" href="@yield('seo_canonical')">
    @else
        <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">
    @endif
    <meta property="og:title" content="@yield('seo_title', config('app.name'))">
    <meta property="og:description" content="@yield('seo_description', '')">
    <meta property="og:type" content="@yield('seo_og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    @if(!empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('seo_title', config('app.name'))">
    <meta name="twitter:description" content="@yield('seo_description', '')">
    @if(!empty($ogImage))
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
