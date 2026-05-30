@extends('layouts.public')

@section('seo_title', __('messages.offices.title') . ' – AGC Assessors')
@section('seo_description', __('messages.offices.subtitle'))

@section('content')

{{-- Hero --}}
<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto pt-14 pb-10">
    <div class="max-w-2xl">
        <span class="inline-block text-[12px] font-semibold tracking-[0.15em] uppercase text-[#00B4D8] mb-4">
            AGC Assessors
        </span>
        <h1 class="font-headline text-[48px] md:text-[60px] font-semibold text-[#00346f] leading-[1.05] tracking-tight mb-5">
            {{ __('messages.offices.title') }}
        </h1>
        <p class="text-[18px] text-[#424751] leading-relaxed font-light max-w-xl">
            {{ __('messages.offices.subtitle') }}
        </p>
    </div>
</section>

{{-- Map --}}
<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto mb-20">
    @if(!empty($officesGeoJson))
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <div id="offices-map-page"
         class="w-full rounded-2xl overflow-hidden border border-[#E2E8F0] shadow-sm"
         style="min-height: 420px;"></div>

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
            m.bindPopup('<strong style="color:#00346f">' + o.name + '</strong><br><span style="color:#64748B;font-size:13px">' + o.address + '</span>');
            m.on('mouseover', function () { m.openPopup(); });
            m.on('mouseout',  function () { m.closePopup(); });
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
    @endif
</section>

{{-- Offices — alternating layout --}}
@if(!empty($offices))
<section class="w-full max-w-[1280px] mx-auto px-6 md:px-8 pb-28">

    {{-- Divider with count --}}
    <div class="flex items-center gap-4 mb-16">
        <div class="h-px flex-1 bg-[#E2E8F0]"></div>
        <span class="text-[13px] font-semibold tracking-[0.12em] uppercase text-[#64748B]">
            {{ count($offices) }} {{ __('messages.offices.offices_count') }}
        </span>
        <div class="h-px flex-1 bg-[#E2E8F0]"></div>
    </div>

    <div class="flex flex-col gap-0">
        @foreach($offices as $i => $office)
        @php $isEven = $i % 2 === 0; @endphp

        <div id="office-{{ $office->id() }}" class="group relative grid grid-cols-1 lg:grid-cols-2 gap-0 mb-20 lg:mb-28 scroll-mt-24">

            {{-- Image side --}}
            <div class="{{ $isEven ? 'lg:order-1' : 'lg:order-2' }} relative overflow-hidden rounded-2xl bg-[#e7e8ef] min-h-[320px] lg:min-h-[420px]">
                @if($office->coverUrl())
                    <img src="{{ $office->coverUrl() }}"
                         alt="{{ $office->name()->get(app()->getLocale()) }}"
                         class="w-full h-full object-cover absolute inset-0 transition-transform duration-700 group-hover:scale-105">
                @else
                    {{-- Placeholder with city initial --}}
                    <div class="absolute inset-0 bg-gradient-to-br from-[#00346f]/10 to-[#00B4D8]/20 flex items-center justify-center">
                        <span class="font-headline text-[120px] font-bold text-[#00346f]/10 select-none leading-none">
                            {{ mb_substr($office->city()->get(app()->getLocale()), 0, 1) }}
                        </span>
                    </div>
                @endif
                {{-- City badge --}}
                <div class="absolute bottom-5 {{ $isEven ? 'left-5' : 'right-5' }} bg-white/95 backdrop-blur-md px-4 py-2 rounded-full shadow-sm">
                    <span class="text-[13px] font-semibold text-[#00346f] tracking-wide">
                        {{ $office->city()->get(app()->getLocale()) }}
                    </span>
                </div>
            </div>

            {{-- Content side --}}
            <div class="{{ $isEven ? 'lg:order-2 lg:pl-16' : 'lg:order-1 lg:pr-16' }} flex flex-col justify-center pt-8 lg:pt-0">

                {{-- Office number --}}
                <span class="text-[11px] font-bold tracking-[0.2em] uppercase text-[#00B4D8] mb-3">
                    {{ str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) }}
                </span>

                {{-- Name --}}
                <h2 class="font-headline text-[32px] md:text-[40px] font-semibold text-[#1E293B] leading-tight tracking-tight mb-4">
                    {{ $office->name()->get(app()->getLocale()) }}
                </h2>

                {{-- Accent line --}}
                <div class="w-12 h-[3px] bg-[#00B4D8] mb-6 rounded-full"></div>

                {{-- Description --}}
                @if($office->description()->get(app()->getLocale()))
                    <p class="text-[16px] text-[#424751] leading-relaxed font-light mb-8">
                        {{ $office->description()->get(app()->getLocale()) }}
                    </p>
                @endif

                {{-- Contact details --}}
                <div class="flex flex-col gap-3 mb-8">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-[18px] text-[#00B4D8] mt-0.5 flex-shrink-0">location_on</span>
                        <span class="text-[15px] text-[#424751] leading-snug">
                            {{ $office->address()->get(app()->getLocale()) }},
                            {{ $office->city()->get(app()->getLocale()) }}
                        </span>
                    </div>

                    @if($office->phone())
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[18px] text-[#64748B] flex-shrink-0">call</span>
                        <a href="tel:{{ $office->phone() }}"
                           class="text-[15px] text-[#424751] hover:text-[#00346f] transition-colors font-medium">
                            {{ $office->phone() }}
                        </a>
                    </div>
                    @endif

                    @if($office->email())
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[18px] text-[#64748B] flex-shrink-0">mail</span>
                        <a href="mailto:{{ $office->email() }}"
                           class="text-[15px] text-[#424751] hover:text-[#00346f] transition-colors">
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

        {{-- Separator between offices (not after last) --}}
        @if(!$loop->last)
        <div class="h-px bg-[#E2E8F0] mb-20 lg:mb-28"></div>
        @endif

        @endforeach
    </div>
</section>
@endif

{{-- Scroll to anchor on load (from home map click) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.location.hash) {
        var el = document.querySelector(window.location.hash);
        if (el) {
            setTimeout(function () {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                el.classList.add('ring-2', 'ring-[#00B4D8]', 'ring-offset-4', 'rounded-2xl');
                setTimeout(function () {
                    el.classList.remove('ring-2', 'ring-[#00B4D8]', 'ring-offset-4', 'rounded-2xl');
                }, 2500);
            }, 300);
        }
    }
});
</script>

@endsection
