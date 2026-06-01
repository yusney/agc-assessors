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
        <link rel="canonical" href="{{ url()->current() }}">
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

    {{-- Resource hints --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://unpkg.com">
    <link rel="dns-prefetch" href="https://tile.openstreetmap.org">

    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400;1,600&display=swap" rel="stylesheet">

    {{-- Material Symbols --}}
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

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
