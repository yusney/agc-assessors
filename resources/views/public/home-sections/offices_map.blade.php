@if(!empty($offices))
@php $officesUrl = LaravelLocalization::getLocalizedURL(app()->getLocale(), '/oficines'); @endphp
<section class="w-full max-w-[1280px] mx-auto px-6 md:px-8 pt-4 pb-16 md:pb-20" id="oficines" aria-label="{{ __('messages.offices.carousel_label') }}">

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
                <span class="material-symbols-outlined ml-1 text-[20px]">&#xe5c8;</span>
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
                'slug'    => $o->publicSlug(app()->getLocale()),
            ];
        }, $offices)));
    @endphp

    @if(!empty($officesForMap))
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <div id="offices-map-home" class="relative z-0 isolate w-full rounded-2xl overflow-hidden mb-10" style="min-height: 400px;"></div>

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
                '<div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">' +
                '<a href="https://www.google.com/maps/dir/?api=1&destination=' + o.lat + ',' + o.lng + '" ' +
                'target="_blank" rel="noopener" ' +
                'style="color:#64748B;font-size:12px;font-weight:600;text-decoration:none;border:1px solid #E2E8F0;padding:4px 10px;border-radius:6px;white-space:nowrap" ' +
                'onmouseover="this.style.borderColor=\'#00346f\';this.style.color=\'#00346f\'" ' +
                'onmouseout="this.style.borderColor=\'#E2E8F0\';this.style.color=\'#64748B\'">' +
                '{{ __("messages.offices.directions") }}</a>' +
                '<a href="' + baseUrl + '/' + o.slug + '" ' +
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

    {{-- Alpine.js carousel component --}}
    @php
        $carouselOffices = array_slice($offices, 0, (int) $section->setting('limit', 6));
        $itemCount = count($carouselOffices);
    @endphp

    <div x-data="officesCarousel" class="relative w-full" x-init="$el.style.setProperty('--items', itemsPerView)">

        {{-- Prev arrow --}}
        <button
            type="button"
            x-on:click="prev()"
            x-on:keydown="onKeydown($event)"
            aria-label="{{ __('messages.offices.carousel_prev') }}"
            class="absolute left-0 top-1/2 -translate-y-1/2 z-10 w-11 h-11 flex items-center justify-center
                   bg-white border border-[#E2E8F0] rounded-full shadow-sm
                   hover:border-[#00346f] hover:shadow-md transition-all duration-300
                   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00B4D8] focus-visible:ring-offset-2">
            <span class="material-symbols-outlined text-[20px] text-[#424751]">chevron_left</span>
        </button>

        {{-- Carousel track --}}
        <div
            class="flex overflow-x-auto snap-x snap-mandatory hide-scrollbar gap-8 mx-10"
            :class="{ 'select-none': isDragging }"
            x-on:scroll.passive="snap()"
            x-on:pointerdown="onPointerDown($event)"
            x-on:pointermove="onPointerMove($event)"
            x-on:pointerup="onPointerUp($event)"
            x-on:pointerleave="isDragging && onPointerUp($event)"
            x-on:click="onClick($event)"
            role="group"
            data-carousel-track
            aria-label="{{ __('messages.offices.carousel_label') }}">

            {{-- Slides: each slide width = (100% - gaps*(items-1)) / items, so N cards fit side-by-side --}}
            @foreach($carouselOffices as $office)
            <div
                class="flex-shrink-0 snap-start"
                :style="`flex: 0 0 calc((100% - (var(--items) - 1) * 2rem) / var(--items));`"
                role="group"
                :aria-label="`{{ $office->name()->get(app()->getLocale()) }} (${firstVisibleIndex + 1} de ${itemCount})`"
                :inert="!isVisible({{ $loop->index }})">

                {{-- Single card (carousel shows N cards per slide via flex layout) --}}
                <div class="h-full bg-white rounded-xl shadow-sm border border-[#E2E8F0] border-l-4 border-l-[#00346f] hover:shadow-md transition-shadow duration-300 p-6 flex flex-col">

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

                    {{-- CTAs: Ver oficina (primary) + Cómo llegar (secondary) --}}
                    <div class="mt-auto pt-3 border-t border-[#E2E8F0] flex flex-col gap-2">
                        @php
                            $cardLocale = $activeLocale ?? (string) config('app.locale');
                            $cardDefault = \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getDefaultLocale();
                            $cardHref = $cardLocale === $cardDefault && config('laravellocalization.hideDefaultLocaleInURL')
                                ? '/oficines/' . $office->publicSlug($cardLocale)
                                : '/' . $cardLocale . '/oficines/' . $office->publicSlug($cardLocale);
                        @endphp
                        <a href="{{ $cardHref }}"
                           class="inline-flex items-center justify-between gap-1 text-[14px] text-[#00346f] font-semibold hover:text-[#00B4D8] transition-colors">
                            {{ __('messages.offices.see_office') }}
                            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </a>
                        @if($office->lat() !== null && $office->lng() !== null)
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $office->lat() }},{{ $office->lng() }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 text-[12px] text-[#64748B] hover:text-[#00346f] transition-colors">
                                {{ __('messages.offices.directions') }}
                                <span class="material-symbols-outlined text-[14px]">&#xf8ce;</span>
                            </a>
                        @endif
                    </div>

                </div>
            </div>
            @endforeach

        </div>

        {{-- Next arrow --}}
        <button
            type="button"
            x-on:click="next()"
            x-on:keydown="onKeydown($event)"
            aria-label="{{ __('messages.offices.carousel_next') }}"
            class="absolute right-0 top-1/2 -translate-y-1/2 z-10 w-11 h-11 flex items-center justify-center
                   bg-white border border-[#E2E8F0] rounded-full shadow-sm
                   hover:border-[#00346f] hover:shadow-md transition-all duration-300
                   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00B4D8] focus-visible:ring-offset-2">
            <span class="material-symbols-outlined text-[20px] text-[#424751]">chevron_right</span>
        </button>

        {{-- Dot indicators --}}
        <div
            class="flex justify-center gap-2 mt-6"
            aria-live="polite"
            x-show="totalPages > 1">
            <template x-for="n in totalPages" :key="n">
                <button
                    type="button"
                    x-on:click="goTo(n - 1)"
                    x-on:keydown="onKeydown($event)"
                    :aria-label="'{{ __('messages.offices.carousel_go_to') }}'.replace(':slide', n)"
                    :aria-current="currentIndex === n - 1 ? 'step' : false"
                    class="w-2.5 h-2.5 rounded-full transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00B4D8] focus-visible:ring-offset-2"
                    :class="currentIndex === n - 1
                        ? 'bg-[#00346f] scale-125'
                        : 'bg-[#E2E8F0] hover:bg-[#00346f]/40'">
                    <span class="sr-only" x-text="'{{ __('messages.offices.carousel_go_to') }}'.replace(':slide', n)"></span>
                </button>
            </template>
        </div>

    </div>

    {{-- Alpine.js component registration (local to Blade, no separate .js file) --}}
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('officesCarousel', () => ({
            currentIndex: 0,
            itemsPerView: 3,
            isDragging: false,
            startX: 0,
            currentX: 0,
            dragDelta: 0,
            DRAG_THRESHOLD: 50,
            itemCount: {{ $itemCount }},
            _track: null,
            _slideStep: 0,

            get totalPages() {
                return Math.ceil(this.itemCount / this.itemsPerView);
            },

            // Index of the first visible slide in the current page. Used to
            // mark off-screen slides as `inert` for accessibility.
            get firstVisibleIndex() {
                return this.currentIndex * this.itemsPerView;
            },

            // Returns true if the given slide index is currently visible in the
            // carousel viewport. Called from the template as `isVisible(i)`.
            isVisible(i) {
                const first = this.firstVisibleIndex;
                return i >= first && i < first + this.itemsPerView;
            },

            init() {
                // Cache the track element and compute initial slide step
                this._track = this.$el.querySelector('[data-carousel-track]');
                this._recomputeSlideStep();

                // Set initial itemsPerView from current window width
                this.updateItemsPerView();

                // Register debounced resize listener
                window.addEventListener('resize', this._debounce(() => {
                    this.updateItemsPerView();
                    this._recomputeSlideStep();
                    // Clamp currentIndex to valid range after resize
                    if (this.currentIndex >= this.totalPages) {
                        this.currentIndex = Math.max(0, this.totalPages - 1);
                    }
                    this.$nextTick(() => this._syncScroll());
                }, 100));

                // Sync scroll position on mount
                this.$nextTick(() => this._syncScroll());
            },

            _recomputeSlideStep() {
                if (!this._track) return;
                const slide = this._track.firstElementChild;
                if (!slide) return;
                this._slideStep = slide.offsetWidth + 32; // gap-8 = 32px
            },

            updateItemsPerView() {
                const w = window.innerWidth;
                this.itemsPerView = w >= 1024 ? 3 : w >= 768 ? 2 : 1;
                // Keep the CSS variable on the carousel root in sync with the JS state
                if (this.$el) {
                    this.$el.style.setProperty('--items', String(this.itemsPerView));
                }
            },

            onPointerDown(e) {
                this._clickSuppressed = false;
                // Activate drag for both touch and mouse pointers so desktop users
                // can also swipe the carousel with click-and-drag, not just touch.
                this.isDragging = true;
                this.startX = e.clientX;
                this.currentX = e.clientX;
                this.dragDelta = 0;
            },

            onPointerMove(e) {
                if (!this.isDragging) return;
                this.currentX = e.clientX;
                this.dragDelta = this.currentX - this.startX;
            },

            onPointerUp(e) {
                if (this.isDragging) {
                    if (Math.abs(this.dragDelta) > this.DRAG_THRESHOLD) {
                        // Drag suppressed click — navigate by direction
                        if (this.dragDelta < 0) {
                            this.next();
                        } else {
                            this.prev();
                        }
                    }
                    this._clickSuppressed = Math.abs(this.dragDelta) > this.DRAG_THRESHOLD;
                    this.isDragging = false;
                    this.dragDelta = 0;
                }
            },

            onClick(e) {
                // Suppress click if drag was detected (delta > threshold)
                // The drag threshold check prevents suppressing intentional taps
                // We store drag suppression in a flag set during pointerup
                if (this._clickSuppressed) {
                    e.preventDefault();
                    e.stopPropagation();
                    this._clickSuppressed = false;
                }
            },

            next() {
                this.currentIndex = this.currentIndex < this.totalPages - 1 ? this.currentIndex + 1 : 0;
                this.$nextTick(() => this._syncScroll());
            },

            prev() {
                this.currentIndex = this.currentIndex > 0 ? this.currentIndex - 1 : this.totalPages - 1;
                this.$nextTick(() => this._syncScroll());
            },

            goTo(n) {
                this.currentIndex = n;
                this.$nextTick(() => this._syncScroll());
            },

            snap() {
                if (!this._track) return;
                if (this._slideStep === 0) return;
                if (this._programmaticScroll) return;
                const rawIndex = Math.round(this._track.scrollLeft / this._slideStep);
                this.currentIndex = Math.max(0, Math.min(rawIndex, this.totalPages - 1));
            },

            onKeydown(e) {
                if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                    this.next();
                    e.preventDefault();
                } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                    this.prev();
                    e.preventDefault();
                }
            },

            _syncScroll() {
                if (!this._track) {
                    this._track = this.$el.querySelector('[data-carousel-track]');
                }
                if (!this._track || this._slideStep === 0) return;
                this._programmaticScroll = true;
                const target = this.currentIndex * this._slideStep;
                // Programmatic smooth scroll. The track intentionally has no
                // `scroll-behavior: smooth` CSS so the `behavior: 'smooth'`
                // option here is respected by the browser.
                this._track.scrollTo({ left: target, behavior: 'smooth' });
                // Release the flag after the smooth scroll has had time to
                // settle, but cap the wait to avoid permanent locks.
                setTimeout(() => {
                    this._programmaticScroll = false;
                }, 800);
            },

            _debounce(fn, delay) {
                let timer;
                const self = this;
                return function(...args) {
                    clearTimeout(timer);
                    timer = setTimeout(() => fn.apply(self, args), delay);
                };
            }
        }));
    });
    </script>

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