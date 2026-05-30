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

{{-- Full-width Google Map --}}
<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto mb-12">
    <div
        x-data="{
            map: null,
            offices: @json($officesGeoJson),
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
        class="w-full rounded-2xl overflow-hidden"
        style="min-height: 500px;">
    </div>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $mapsApiKey }}&callback=Function.prototype"></script>
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
