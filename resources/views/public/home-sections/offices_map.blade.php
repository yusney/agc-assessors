@if(!empty($offices))
@php $officesUrl = LaravelLocalization::getLocalizedURL(app()->getLocale(), '/oficines'); @endphp
<section class="w-full max-w-[1280px] mx-auto px-6 md:px-8 pt-4 pb-16 md:pb-20" id="oficines">

    {{-- Section header: title left, optional CTA right (same pattern as news_highlight) --}}
    <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-6">
        <div class="max-w-2xl">
            <h2 class="font-headline text-[36px] md:text-[48px] text-[#1E293B] mb-4 tracking-tight font-semibold leading-none">
                {{ $section->localized('title') }}
            </h2>
            @if($section->localized('subtitle'))
                <p class="text-[20px] text-[#64748B] font-light">{{ $section->localized('subtitle') }}</p>
            @endif
        </div>

        @if($section->localized('cta_label') && $section->cta_url)
            <a href="{{ $officesUrl }}"
               class="hidden md:inline-flex items-center text-[#1E293B] font-medium text-[15px] hover:text-[#00346f] transition-colors pb-1 border-b-2 border-transparent hover:border-[#00346f]">
                {{ $section->localized('cta_label') }}
                <span class="material-symbols-outlined ml-1 text-[20px]">arrow_forward</span>
            </a>
        @endif
    </div>

    {{-- Leaflet map — only offices with lat+lng --}}
    @php
        $officesForMap = array_values(array_filter(array_map(function ($o) {
            if ($o->lat() === null || $o->lng() === null) return null;
            return [
                'id'      => $o->id(),
                'name'    => $o->name()->get(app()->getLocale()),
                'address' => $o->address()->get(app()->getLocale()),
                'lat'     => $o->lat(),
                'lng'     => $o->lng(),
            ];
        }, $offices)));
    @endphp

    @if(!empty($officesForMap))
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <div id="offices-map-home" class="w-full rounded-2xl overflow-hidden mb-10" style="min-height: 400px;"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var offices = @json($officesForMap);
        var baseUrl  = @json($officesUrl);
        var map = L.map('offices-map-home', { scrollWheelZoom: false });

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
                'onmouseout="this.style.borderColor=\'#E2E8F0\';this.style.color=\'#64748B\'">' +
                '{{ __("messages.offices.directions") }}</a>' +
                '<a href="' + baseUrl + '#office-' + o.id + '" ' +
                'style="color:#fff;font-size:12px;font-weight:600;text-decoration:none;background:#00346f;padding:4px 10px;border-radius:6px;white-space:nowrap" ' +
                'onmouseover="this.style.background=\'#00B4D8\'" ' +
                'onmouseout="this.style.background=\'#00346f\'">' +
                '{{ __("messages.offices.see_office") }}</a>' +
                '</div></div>'
            );
            m.on('mouseover', function () { m.openPopup(); });
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

    {{-- Office card grid (max $limit offices) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach(array_slice($offices, 0, (int) $section->setting('limit', 6)) as $office)
            <div class="bg-white rounded-xl shadow-sm border border-[#E2E8F0] border-l-4 border-l-[#00346f] hover:shadow-md transition-shadow duration-300 p-6 flex flex-col">

                {{-- Name --}}
                <h3 class="font-headline text-[18px] font-semibold text-[#1E293B] mb-3">
                    {{ $office->name()->get(app()->getLocale()) }}
                </h3>

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

    {{-- Mobile CTA --}}
    @if($section->localized('cta_label') && $section->cta_url)
        <div class="mt-10 text-center md:hidden">
            <a href="{{ $officesUrl }}" class="btn-outline w-full justify-center">
                {{ $section->localized('cta_label') }}
            </a>
        </div>
    @endif

</section>
@endif
