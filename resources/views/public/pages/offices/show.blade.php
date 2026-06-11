@extends('layouts.public')

@section('seo_title', \Illuminate\Support\Str::limit(
    __('messages.offices.office_in') . ' ' . ($office->city()->get(app()->getLocale()) ?? $office->city()->get('ca')) . ' | AGC Assessors',
    60,
    ''
))
@section('seo_description', \Illuminate\Support\Str::limit(
    $office->description()->get(app()->getLocale()) ?? $office->description()->get('ca') ?? __('messages.offices.seo_description'),
    160
))
@section('seo_og_type', 'business.business')

@section('content')

@php
    // Resolve the active locale from the URL via the SeoComposer. Using
    // app()->getLocale() would be stale at view-render time.
    $activeLocale = $activeLocale ?? (string) config('app.locale');
    $defaultLocale = \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getDefaultLocale();
    $hideDefault = (bool) config('laravellocalization.hideDefaultLocaleInURL', false);

    /**
     * Build a localized URL for a given path. Avoids route() because
     * the package has three route groups sharing the same name (one
     * per locale) and route() would emit ?locale= query strings.
     */
    $localized = function (string $path) use ($activeLocale, $defaultLocale, $hideDefault): string {
        if ($activeLocale === $defaultLocale && $hideDefault) {
            return $path;
        }
        return '/' . $activeLocale . $path;
    };

    $locale = $activeLocale;
    $city = $office->city()->get($locale) ?? $office->city()->get('ca');
    $address = $office->address()->get($locale) ?? $office->address()->get('ca');
    $altText = $office->imageAlt()?->get($locale) ?? $office->imageAlt()?->get('ca') ?? $city;
    $description = $office->description()->get($locale) ?? $office->description()->get('ca') ?? '';
    $hours = $office->openingHours()?->get($locale) ?? $office->openingHours()?->get('ca') ?? '';
    $managerName = $office->managerName()?->get($locale) ?? $office->managerName()?->get('ca') ?? '';
    $managerRole = $office->managerRole()?->get($locale) ?? $office->managerRole()?->get('ca') ?? '';
    $managerBio = $office->managerBio()?->get($locale) ?? $office->managerBio()?->get('ca') ?? '';
    $serviceArea = $office->serviceAreaList($locale);
@endphp

{{-- Hero with breadcrumb --}}
<section class="relative w-full overflow-hidden bg-[#f9f9ff]">
    <div class="relative z-10 w-full max-w-[1280px] mx-auto px-6 md:px-8 pt-10 pb-12">
        {{-- Breadcrumb --}}
        <nav aria-label="{{ __('messages.breadcrumb.label') }}" class="mb-6 text-[13px] text-[#64748B]">
            <ol class="flex flex-wrap items-center gap-1.5">
                <li><a href="{{ $localized('/') }}" class="hover:text-[#00346f] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00346f] rounded">{{ __('messages.nav.home') }}</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ $localized('/oficines') }}" class="hover:text-[#00346f] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00346f] rounded">{{ __('messages.offices.title') }}</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-[#1E293B] font-semibold">{{ $city }}</li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center">
            {{-- Left: title + description --}}
            <div>
                <span class="inline-block text-[13px] font-semibold uppercase tracking-[0.22em] text-[#00346f] mb-3">
                    AGC Assessors
                </span>
                <h1 class="font-headline text-[36px] md:text-[52px] font-semibold text-[#1E293B] leading-[1.05] tracking-tight mb-4 text-balance">
                    {{ __('messages.offices.office_in') }} {{ $city }}
                </h1>
                @if($description !== '')
                <p class="text-[17px] text-[#64748B] leading-relaxed font-light max-w-2xl">
                    {{ $description }}
                </p>
                @endif
            </div>

            {{-- Right: office cover image (only if uploaded) --}}
            @if($office->coverUrl())
            <div class="relative aspect-[4/3] rounded-[1.5rem] overflow-hidden border border-[#E2E8F0] shadow-md">
                <img src="{{ $office->coverUrl() }}"
                     alt="{{ $altText }}"
                     itemprop="image"
                     width="1200"
                     height="900"
                     loading="eager"
                     fetchpriority="high"
                     decoding="async"
                     class="w-full h-full object-cover">
            </div>
            @else
            <div class="relative aspect-[4/3] rounded-[1.5rem] overflow-hidden border border-[#E2E8F0] bg-gradient-to-br from-[#00346f]/8 to-[#00B4D8]/15 flex items-center justify-center">
                <div class="text-center">
                    <span class="font-headline text-[120px] font-bold text-[#00346f]/15 select-none leading-none">
                        {{ mb_substr($city, 0, 1) }}
                    </span>
                    <p class="text-[14px] text-[#64748B] mt-2 px-6">
                        {{ __('messages.offices.cover_upload_hint') }}
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>

{{-- Map --}}
@if(!empty($officeGeoJson))
<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto mb-12">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <div id="office-map-show"
         class="relative z-0 isolate w-full rounded-[2rem] overflow-hidden border border-[#E2E8F0] shadow-lg"
         style="min-height: 320px;"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var offices = @json($officeGeoJson);
        var map = L.map('office-map-show', { scrollWheelZoom: false, zoomControl: true });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(map);

        if (offices.length > 0) {
            var o = offices[0];
            map.setView([o.lat, o.lng], 15);
            var m = L.marker([o.lat, o.lng]).addTo(map);
            m.bindPopup(
                '<div style="min-width:180px;font-family:inherit">' +
                '<strong style="color:#00346f;font-size:14px;display:block;margin-bottom:4px">AGC Assessors - ' + o.name + '</strong>' +
                '<span style="color:#64748B;font-size:13px;display:block">' + o.address + '</span>' +
                '</div>'
            ).openPopup();
        }
    });
    </script>
</section>
@endif

{{-- Main content: NAP + Hours + Manager + Service area + CTAs --}}
<section class="w-full max-w-[1280px] mx-auto px-6 md:px-8 pb-28">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Column 1: NAP + Hours --}}
        <div class="lg:col-span-1 bg-white rounded-[1.5rem] border border-[#E2E8F0] p-6">
            <h2 class="font-headline text-[20px] font-semibold text-[#1E293B] mb-4">
                {{ __('messages.offices.contact_heading') }}
            </h2>

            <address itemscope itemtype="https://schema.org/PostalAddress" class="not-italic flex flex-col gap-3">
                <meta itemprop="streetAddress" content="{{ $address }}">
                <meta itemprop="addressLocality" content="{{ $city }}">
                <meta itemprop="addressRegion" content="Barcelona">
                <meta itemprop="addressCountry" content="ES">

                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-[20px] text-[#00B4D8] mt-0.5 flex-shrink-0" aria-hidden="true">location_on</span>
                    <span class="text-[14px] text-[#424751] leading-snug" itemprop="streetAddress">
                        {{ $address }}
                    </span>
                </div>

                @if($office->phone())
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px] text-[#64748B] flex-shrink-0" aria-hidden="true">call</span>
                    <a href="tel:{{ $office->phone() }}"
                       itemprop="telephone"
                       class="text-[14px] text-[#424751] hover:text-[#00346f] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00346f] rounded transition-colors">
                        {{ $office->phone() }}
                    </a>
                </div>
                @endif

                @if($office->email())
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px] text-[#64748B] flex-shrink-0" aria-hidden="true">mail</span>
                    <a href="mailto:{{ $office->email() }}"
                       itemprop="email"
                       class="text-[14px] text-[#424751] hover:text-[#00346f] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00346f] rounded transition-colors break-all">
                        {{ $office->email() }}
                    </a>
                </div>
                @endif
            </address>

            @if($hours !== '')
            <div class="mt-6 pt-6 border-t border-[#E2E8F0]">
                <h3 class="text-[12px] font-semibold uppercase tracking-[0.08em] text-[#64748B] mb-3">
                    {{ __('messages.offices.col_hours') }}
                </h3>
                <p class="text-[14px] text-[#424751] leading-relaxed whitespace-pre-line">
                    {{ $hours }}
                </p>
            </div>
            @endif

            @if($office->lat() !== null && $office->lng() !== null)
            <div class="mt-6">
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $office->lat() }},{{ $office->lng() }}"
                   target="_blank" rel="noopener noreferrer"
                   itemprop="hasMap"
                   class="inline-flex items-center justify-center gap-2 w-full px-4 py-3 bg-[#00346f] text-white text-[14px] font-semibold rounded-full hover:bg-[#004a99] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00346f] focus-visible:ring-offset-2 transition-colors">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">directions</span>
                    {{ __('messages.offices.directions') }}
                </a>
            </div>
            @endif
        </div>

        {{-- Column 2: Manager + Service area --}}
        <div class="lg:col-span-2 flex flex-col gap-6">

            {{-- Manager --}}
            @if($managerName !== '')
            <div class="bg-white rounded-[1.5rem] border border-[#E2E8F0] p-6">
                <span class="inline-block text-[12px] font-semibold uppercase tracking-[0.08em] text-[#64748B] mb-2">
                    {{ __('messages.offices.manager_label') }}
                </span>
                <h2 class="font-headline text-[22px] font-semibold text-[#1E293B] mb-1">
                    {{ $managerName }}
                </h2>
                @if($managerRole !== '')
                <p class="text-[14px] text-[#00346f] font-medium mb-3">
                    {{ $managerRole }}
                </p>
                @endif
                @if($managerBio !== '')
                <p class="text-[15px] text-[#424751] leading-relaxed">
                    {{ $managerBio }}
                </p>
                @endif
            </div>
            @endif

            {{-- Service area --}}
            @if($serviceArea !== [])
            <div class="bg-white rounded-[1.5rem] border border-[#E2E8F0] p-6">
                <h2 class="font-headline text-[20px] font-semibold text-[#1E293B] mb-2">
                    {{ __('messages.offices.also_serving') }}
                </h2>
                <p class="text-[14px] text-[#64748B] mb-4">
                    {{ __('messages.offices.also_serving_intro', ['city' => $city]) }}
                </p>
                <ul class="flex flex-wrap gap-2">
                    @foreach($serviceArea as $area)
                    <li class="text-[13px] text-[#00346f] bg-[#00346f]/8 px-3 py-1.5 rounded-full border border-[#00346f]/15">
                        {{ $area }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Common content CTAs --}}
            <div class="bg-white rounded-[1.5rem] border border-[#E2E8F0] p-6">
                <h2 class="font-headline text-[20px] font-semibold text-[#1E293B] mb-4">
                    {{ __('messages.offices.common_content_heading') }}
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ $localized('/serveis') }}"
                       class="flex items-center gap-3 p-3 rounded-xl border border-[#E2E8F0] hover:border-[#00346f] hover:bg-[#F8FAFC] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00346f] transition-colors">
                        <span class="material-symbols-outlined text-[24px] text-[#00B4D8]" aria-hidden="true">business_center</span>
                        <span class="text-[14px] font-semibold text-[#1E293B]">{{ __('messages.nav.services') }}</span>
                    </a>
                    <a href="{{ $localized('/equip') }}"
                       class="flex items-center gap-3 p-3 rounded-xl border border-[#E2E8F0] hover:border-[#00346f] hover:bg-[#F8FAFC] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00346f] transition-colors">
                        <span class="material-symbols-outlined text-[24px] text-[#00B4D8]" aria-hidden="true">groups</span>
                        <span class="text-[14px] font-semibold text-[#1E293B]">{{ __('messages.nav.team') }}</span>
                    </a>
                    <a href="{{ $localized('/actualitat') }}"
                       class="flex items-center gap-3 p-3 rounded-xl border border-[#E2E8F0] hover:border-[#00346f] hover:bg-[#F8FAFC] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00346f] transition-colors">
                        <span class="material-symbols-outlined text-[24px] text-[#00B4D8]" aria-hidden="true">article</span>
                        <span class="text-[14px] font-semibold text-[#1E293B]">{{ __('messages.nav.news') }}</span>
                    </a>
                    <a href="{{ $localized('/contacte') }}"
                       class="flex items-center gap-3 p-3 rounded-xl border border-[#E2E8F0] hover:border-[#00346f] hover:bg-[#F8FAFC] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00346f] transition-colors">
                        <span class="material-symbols-outlined text-[24px] text-[#00B4D8]" aria-hidden="true">mail</span>
                        <span class="text-[14px] font-semibold text-[#1E293B]">{{ __('messages.nav.contact') }}</span>
                    </a>
                </div>

                <div class="mt-5 pt-5 border-t border-[#E2E8F0]">
                    <a href="{{ $localized('/oficines') }}"
                       class="inline-flex items-center gap-2 text-[14px] font-semibold text-[#00346f] border-b-2 border-[#00346f]/30 hover:border-[#00346f] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00346f] rounded pb-0.5 transition-colors">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
                        {{ __('messages.offices.back_to_all') }}
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection
