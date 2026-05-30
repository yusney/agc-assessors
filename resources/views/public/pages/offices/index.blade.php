@extends('layouts.public')

@section('seo_title', __('messages.offices.title') . ' – AGC Assessors')
@section('seo_description', __('messages.offices.subtitle'))

@section('content')

{{-- Hero header (same pattern as news/index.blade.php) --}}
<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto py-12 md:py-16">
    <h1 class="font-headline text-[48px] font-semibold text-[#00346f] mb-3 tracking-tight leading-none">
        {{ __('messages.offices.title') }}
    </h1>
    <p class="text-[18px] text-[#424751] max-w-2xl leading-relaxed font-light">
        {{ __('messages.offices.subtitle') }}
    </p>
</section>

{{-- Full-width Leaflet map --}}
<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto mb-12">
    @if(!empty($officesGeoJson))
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WLs=" crossorigin=""></script>

    <div id="offices-map-page" class="w-full rounded-2xl overflow-hidden" style="min-height: 500px;"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var offices = @json($officesGeoJson);
        var map = L.map('offices-map-page', { scrollWheelZoom: false });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(map);

        var markers = offices.map(function (o) {
            var m = L.marker([o.lat, o.lng]).addTo(map);
            m.bindPopup('<strong>' + o.name + '</strong><br>' + o.address);
            return m;
        });

        if (markers.length === 1) {
            map.setView([offices[0].lat, offices[0].lng], 14);
        } else if (markers.length > 1) {
            var group = L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.2));
        } else {
            map.setView([41.3879, 2.16992], 7);
        }
    });
    </script>
    @endif
</section>

{{-- Offices card grid --}}
<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto pb-24">
    @if(empty($offices))
        <p class="text-[#64748B] text-center py-16">{{ __('messages.offices.empty') }}</p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($offices as $office)
                <div class="bg-white rounded-xl shadow-sm border border-[#E2E8F0] border-l-4 border-l-[#00346f] hover:shadow-md transition-shadow duration-300 p-6 flex flex-col">

                    {{-- Name --}}
                    <h2 class="font-headline text-[18px] font-semibold text-[#1E293B] mb-3">
                        {{ $office->name()->get(app()->getLocale()) }}
                    </h2>

                    {{-- Address + city --}}
                    <div class="flex items-start gap-2 mb-2">
                        <span class="material-symbols-outlined text-[#00B4D8] text-[18px] flex-shrink-0 mt-0.5">location_on</span>
                        <span class="text-[14px] text-[#64748B] leading-snug">
                            {{ $office->address()->get(app()->getLocale()) }},
                            {{ $office->city()->get(app()->getLocale()) }}
                        </span>
                    </div>

                    {{-- Phone (optional) --}}
                    @if($office->phone())
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-[#64748B] text-[18px] flex-shrink-0">call</span>
                            <a href="tel:{{ $office->phone() }}"
                               class="text-[14px] text-[#424751] hover:text-[#00346f] transition-colors">
                                {{ $office->phone() }}
                            </a>
                        </div>
                    @endif

                    {{-- Email (optional) --}}
                    @if($office->email())
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-[#64748B] text-[18px] flex-shrink-0">mail</span>
                            <a href="mailto:{{ $office->email() }}"
                               class="text-[14px] text-[#424751] hover:text-[#00346f] transition-colors">
                                {{ $office->email() }}
                            </a>
                        </div>
                    @endif

                    {{-- Directions CTA (only if lat+lng set) --}}
                    @if($office->lat() !== null && $office->lng() !== null)
                        <div class="mt-auto pt-3 border-t border-[#E2E8F0]">
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $office->lat() }},{{ $office->lng() }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 text-[13px] text-[#00346f] font-medium hover:text-[#00B4D8] transition-colors">
                                {{ __('messages.offices.directions') }}
                                <span class="material-symbols-outlined text-[16px]">arrow_outward</span>
                            </a>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    @endif
</section>

@endsection
