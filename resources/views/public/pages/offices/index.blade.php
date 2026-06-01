@extends('layouts.public')

@section('seo_title', __('messages.offices.title') . ' – AGC Assessors')
@section('seo_description', __('messages.offices.subtitle'))

@section('content')

{{-- Hero with Map Background --}}
<section class="relative w-full overflow-hidden bg-[#f9f9ff]">
    {{-- Map background --}}
    <div class="absolute inset-0 z-0 opacity-20">
        <svg viewBox="0 0 400 400" class="w-full h-full" preserveAspectRatio="xMidYMid meet">
            <defs>
                <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#00346f" stroke-width="0.5" opacity="0.3"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)" />
            {{-- Simplified Catalonia outline --}}
            <path d="M180,80 L220,85 L260,90 L280,120 L290,160 L285,200 L270,240 L250,270 L220,280 L190,275 L160,260 L140,230 L130,190 L135,150 L150,110 L165,90 Z"
                  fill="#00346f" opacity="0.1" stroke="#00346f" stroke-width="1"/>
        </svg>
    </div>

    <div class="relative z-10 w-full max-w-[1280px] mx-auto px-6 md:px-8 pt-20 pb-16 md:pt-28 md:pb-20">
        <div class="max-w-2xl mx-auto text-center">
            <span class="inline-block text-[13px] font-semibold uppercase tracking-[0.22em] text-[#00346f] mb-5">
                AGC Assessors
            </span>
            <h1 class="font-headline text-[40px] md:text-[56px] font-semibold text-[#1E293B] leading-[1.05] tracking-tight mb-5">
                {{ __('messages.offices.title') }}
            </h1>
            <p class="text-[18px] text-[#64748B] leading-relaxed font-light max-w-xl mx-auto">
                {{ __('messages.offices.subtitle') }}
            </p>
        </div>
    </div>
</section>

{{-- Map Section --}}
@if(!empty($officesGeoJson))
<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto mb-16 -mt-8 relative z-20">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <div id="offices-map-page"
         class="w-full rounded-[2rem] overflow-hidden border border-[#E2E8F0] shadow-lg"
         style="min-height: 380px;"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var offices = @json($officesGeoJson);
        var map = L.map('offices-map-page', { scrollWheelZoom: false, zoomControl: true });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(map);

        var markers = offices.map(function (o) {
            var m = L.marker([o.lat, o.lng]).addTo(map);
            m.bindPopup(
                '<div style="min-width:200px;font-family:inherit">' +
                '<strong style="color:#00346f;font-size:14px;display:block;margin-bottom:4px">' + o.name + '</strong>' +
                '<span style="color:#64748B;font-size:13px;display:block;margin-bottom:10px">' + o.address + '</span>' +
                '<div style="display:flex;gap:10px;align-items:center">' +
                '<a href="https://www.google.com/maps/dir/?api=1&destination=' + o.lat + ',' + o.lng + '" ' +
                'target="_blank" rel="noopener" ' +
                'style="color:#64748B;font-size:12px;font-weight:600;text-decoration:none;border:1px solid #E2E8F0;padding:4px 10px;border-radius:6px;white-space:nowrap" ' +
                'onmouseover="this.style.borderColor=\'#00346f\';this.style.color=\'#00346f\'" ' +
                'onmouseout="this.style.borderColor=\'#E2E8F0\';this.style.color=\'#64748B\'">{{ __("messages.offices.directions") }}</a>' +
                '</div></div>'
            );
            m.on('mouseover', function () { m.openPopup(); });
            return m;
        });

        if (markers.length === 1) {
            map.setView([offices[0].lat, offices[0].lng], 14);
        } else if (markers.length > 1) {
            map.fitBounds(L.featureGroup(markers).getBounds().pad(0.2));
        } else {
            map.setView([41.3879, 2.16992], 7);
        }
    });
    </script>
</section>
@endif

{{-- Divider --}}
@if(!empty($offices))
<div class="w-full max-w-[1280px] mx-auto px-6 md:px-8 mb-12">
    <div class="flex items-center gap-4">
        <div class="h-px flex-1 bg-[#E2E8F0]"></div>
        <span class="text-[13px] font-semibold tracking-[0.12em] uppercase text-[#64748B]">
            {{ count($offices) }} {{ __('messages.offices.offices_count') }}
        </span>
        <div class="h-px flex-1 bg-[#E2E8F0]"></div>
    </div>
</div>

{{-- Offices Grid --}}
<section class="w-full max-w-[1280px] mx-auto px-6 md:px-8 pb-28">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($offices as $office)
        <div id="office-{{ $office->id() }}" class="group bg-white rounded-[1.5rem] border border-[#E2E8F0] overflow-hidden hover:shadow-xl transition-all duration-500 hover:-translate-y-1">
            {{-- Image --}}
            <div class="relative h-[220px] overflow-hidden">
                @if($office->coverUrl())
                    <img src="{{ $office->coverUrl() }}"
                         alt="{{ $office->name()->get(app()->getLocale()) }}"
                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-[#00346f]/10 to-[#00B4D8]/20 flex items-center justify-center">
                        <span class="font-headline text-[80px] font-bold text-[#00346f]/10 select-none">
                            {{ mb_substr($office->city()->get(app()->getLocale()), 0, 1) }}
                        </span>
                    </div>
                @endif
                {{-- City badge --}}
                <div class="absolute top-4 left-4 bg-white/95 backdrop-blur-md px-4 py-1.5 rounded-full shadow-sm">
                    <span class="text-[13px] font-semibold text-[#00346f]">
                        {{ $office->city()->get(app()->getLocale()) }}
                    </span>
                </div>
            </div>

            {{-- Content --}}
            <div class="p-6">
                <h3 class="font-headline text-[20px] font-semibold text-[#1E293B] mb-3 leading-tight">
                    {{ $office->name()->get(app()->getLocale()) }}
                </h3>

                {{-- Contact info --}}
                <div class="flex flex-col gap-2.5 mb-6">
                    <div class="flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-[18px] text-[#00B4D8] mt-0.5 flex-shrink-0">location_on</span>
                        <span class="text-[14px] text-[#424751] leading-snug">
                            {{ $office->address()->get(app()->getLocale()) }}
                        </span>
                    </div>

                    @if($office->phone())
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-[18px] text-[#64748B] flex-shrink-0">call</span>
                        <a href="tel:{{ $office->phone() }}"
                           class="text-[14px] text-[#424751] hover:text-[#00346f] transition-colors">
                            {{ $office->phone() }}
                        </a>
                    </div>
                    @endif

                    @if($office->email())
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-[18px] text-[#64748B] flex-shrink-0">mail</span>
                        <a href="mailto:{{ $office->email() }}"
                           class="text-[14px] text-[#424751] hover:text-[#00346f] transition-colors">
                            {{ $office->email() }}
                        </a>
                    </div>
                    @endif
                </div>

                {{-- CTA --}}
                @if($office->lat() !== null && $office->lng() !== null)
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $office->lat() }},{{ $office->lng() }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 text-[14px] font-semibold text-[#00346f] border-b-2 border-[#00346f]/30 hover:border-[#00346f] pb-0.5 transition-colors w-fit group/link">
                    {{ __('messages.offices.directions') }}
                    <span class="material-symbols-outlined text-[16px] transition-transform group-hover/link:translate-x-0.5 group-hover/link:-translate-y-0.5">arrow_outward</span>
                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- Scroll to anchor on load --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.location.hash) {
        var el = document.querySelector(window.location.hash);
        if (el) {
            setTimeout(function () {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.classList.add('ring-2', 'ring-[#00B4D8]', 'ring-offset-4', 'rounded-[1.5rem]');
                setTimeout(function () {
                    el.classList.remove('ring-2', 'ring-[#00B4D8]', 'ring-offset-4', 'rounded-[1.5rem]');
                }, 2500);
            }, 300);
        }
    }
});
</script>

@endsection
