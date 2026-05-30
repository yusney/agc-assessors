@if(!empty($offices))
<section class="w-full max-w-[1280px] mx-auto px-6 md:px-8 py-16 md:py-20" id="oficines">

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
            <a href="{{ url($section->cta_url) }}"
               class="hidden md:inline-flex items-center text-[#1E293B] font-medium text-[15px] hover:text-[#00346f] transition-colors pb-1 border-b-2 border-transparent hover:border-[#00346f]">
                {{ $section->localized('cta_label') }}
                <span class="material-symbols-outlined ml-1 text-[20px]">arrow_forward</span>
            </a>
        @endif
    </div>

    {{-- Google Maps — build $officesForMap: only offices with lat+lng --}}
    @php
        $officesForMap = array_values(array_filter(array_map(function ($o) {
            if ($o->lat() === null || $o->lng() === null) {
                return null;
            }
            return [
                'name'    => $o->name()->get(app()->getLocale()),
                'address' => $o->address()->get(app()->getLocale()),
                'lat'     => $o->lat(),
                'lng'     => $o->lng(),
            ];
        }, $offices)));
    @endphp

    <div
        x-data="{
            map: null,
            offices: @json($officesForMap),
            init() {
                this.map = new google.maps.Map(this.$el, {
                    zoom: 7,
                    center: { lat: 41.3879, lng: 2.16992 },
                    styles: [{ featureType: 'all', stylers: [{ saturation: -20 }] }]
                });
                this.offices.forEach(o => {
                    const marker = new google.maps.Marker({ position: { lat: o.lat, lng: o.lng }, map: this.map, title: o.name });
                    const iw = new google.maps.InfoWindow({ content: '<b>' + o.name + '</b><br>' + o.address });
                    marker.addListener('click', () => iw.open(this.map, marker));
                });
            }
        }"
        class="w-full rounded-2xl overflow-hidden mb-10"
        style="min-height: 400px;">
    </div>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $mapsApiKey ?? '' }}&callback=Function.prototype"></script>

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
            <a href="{{ url($section->cta_url) }}" class="btn-outline w-full justify-center">
                {{ $section->localized('cta_label') }}
            </a>
        </div>
    @endif

</section>
@endif
