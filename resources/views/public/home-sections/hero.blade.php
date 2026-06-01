@php
    $heroSlides = data_get($section->settings, 'hero_slides', []);

    // Build the slides array: filter entries that have at least one image source
    $slides = collect($heroSlides)
        ->map(function (array $slide): array {
            $url = null;
            if (!empty($slide['media_id'])) {
                $url = \Awcodes\Curator\Models\Media::find($slide['media_id'])?->url;
            }
            if (!$url && !empty($slide['image_url'])) {
                $url = $slide['image_url'];
            }
            return ['url' => $url, 'alt' => $slide['image_alt'] ?? ''];
        })
        ->filter(fn (array $s): bool => !empty($s['url']))
        ->values()
        ->all();

    // Fallback: single legacy image
    if (empty($slides)) {
        $singleUrl = $section->main_image_media_id
            ? (\Awcodes\Curator\Models\Media::find($section->main_image_media_id)?->url)
            : $section->image_url;

        if ($singleUrl) {
            $slides = [[
                'url' => $singleUrl,
                'alt' => data_get($section->settings, 'image_alt.' . app()->getLocale(), $section->localized('title') ?? ''),
            ]];
        }
    }

    $transition  = data_get($section->settings, 'hero_transition', 'fade');
    $interval    = (int) data_get($section->settings, 'hero_interval', 5);
    $autoplay    = (bool) data_get($section->settings, 'hero_autoplay', true);
    $slidesJson  = json_encode($slides, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
@endphp

<section class="w-full max-w-[1280px] mx-auto px-6 md:px-8 pt-20 pb-32 md:pt-32 md:pb-48 grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-20 items-center relative">

    {{-- ── Text content (left column) ────────────────────────── --}}
    <div class="lg:col-span-6 space-y-8 relative z-10">
        @if($section->localized('eyebrow'))
            <p class="text-[13px] font-semibold uppercase tracking-[0.22em] text-[#00346f]">{{ $section->localized('eyebrow') }}</p>
        @endif

        <h1 class="font-headline text-[48px] lg:text-[72px] text-[#0f172a] leading-[1.05] tracking-[-0.02em] font-bold">
            {!! $section->localized('title') !!}
        </h1>

        @if($section->localized('subtitle'))
            <p class="text-[20px] text-[#64748B] max-w-xl leading-relaxed font-light">{{ $section->localized('subtitle') }}</p>
        @endif

        <div class="pt-2 flex flex-col sm:flex-row gap-5 items-start">
            @if($section->localized('cta_label') && $section->cta_url)
                <a href="{{ url($section->cta_url) }}" class="btn-primary text-[16px] px-10 py-4">
                    {{ $section->localized('cta_label') }}
                    <span class="material-symbols-outlined text-[20px]">arrow_right_alt</span>
                </a>
            @endif

            @if($section->localized('secondary_cta_label') && $section->secondary_cta_url)
                <a href="{{ url($section->secondary_cta_url) }}" class="btn-outline text-[16px] px-10 py-4">
                    {{ $section->localized('secondary_cta_label') }}
                </a>
            @endif
        </div>
    </div>

    {{-- ── Image / Slider (right column) ─────────────────────── --}}
    <div class="lg:col-span-6 mt-12 lg:mt-0 relative h-full min-h-[400px] lg:min-h-[560px]">
        <div class="absolute inset-0 bg-[#f3f3fa] rounded-[2rem] lg:rounded-[2.5rem] rotate-3 scale-105 opacity-50 z-0"></div>
        <div class="absolute inset-0 rounded-[2rem] lg:rounded-[2.5rem] overflow-hidden shadow-2xl z-10 border border-white/50 bg-gradient-to-tr from-[#f1f5f9] to-[#ffffff]">

            @if(!empty($slides))
                <div
                    x-data="{
                        current: 0,
                        slides: {{ $slidesJson }},
                        transition: '{{ $transition }}',
                        interval: {{ $interval }} * 1000,
                        autoplay: {{ $autoplay ? 'true' : 'false' }},
                        timer: null,

                        init() {
                            if (this.autoplay && this.slides.length > 1) {
                                this.startAutoplay();
                            }
                        },

                        startAutoplay() {
                            this.timer = setInterval(() => this.next(), this.interval);
                        },

                        stopAutoplay() {
                            clearInterval(this.timer);
                        },

                        next() {
                            this.current = (this.current + 1) % this.slides.length;
                        },

                        prev() {
                            this.current = (this.current - 1 + this.slides.length) % this.slides.length;
                        },

                        goTo(index) {
                            this.current = index;
                        }
                    }"
                    @mouseenter="stopAutoplay()"
                    @mouseleave="autoplay && slides.length > 1 && startAutoplay()"
                    class="relative w-full h-full"
                >
                    {{-- Slides --}}
                    <template x-for="(slide, index) in slides" :key="index">
                        <div
                            class="absolute inset-0 w-full h-full"
                            :style="transition === 'slide'
                                ? (current === index
                                    ? 'opacity:1;transform:translateX(0);transition:transform 0.7s ease-in-out,opacity 0.7s ease-in-out;z-index:2'
                                    : (current > index
                                        ? 'opacity:0;transform:translateX(-100%);transition:transform 0.7s ease-in-out,opacity 0.7s ease-in-out;z-index:1'
                                        : 'opacity:0;transform:translateX(100%);transition:transform 0.7s ease-in-out,opacity 0.7s ease-in-out;z-index:1'))
                                : (transition === 'zoom'
                                    ? (current === index
                                        ? 'opacity:1;transform:scale(1);transition:transform 0.7s ease-in-out,opacity 0.7s ease-in-out;z-index:2'
                                        : 'opacity:0;transform:scale(1.1);transition:transform 0.7s ease-in-out,opacity 0.7s ease-in-out;z-index:1')
                                    : (current === index
                                        ? 'opacity:1;transition:opacity 0.7s ease-in-out;z-index:2'
                                        : 'opacity:0;transition:opacity 0.7s ease-in-out;z-index:1'))"
                        >
                            <img
                                :src="slide.url"
                                :alt="slide.alt"
                                class="w-full h-full object-cover object-center"
                            >
                        </div>
                    </template>

                    {{-- Dot navigation (only when multiple slides) --}}
                    <template x-if="slides.length > 1">
                        <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-2 z-20">
                            <template x-for="(slide, index) in slides" :key="index">
                                <button
                                    @click="goTo(index)"
                                    :class="current === index
                                        ? 'w-6 h-2 bg-white rounded-full'
                                        : 'w-2 h-2 bg-white/50 rounded-full hover:bg-white/80'"
                                    class="transition-all duration-300"
                                    :aria-label="'Ir a diapositiva ' + (index + 1)"
                                ></button>
                            </template>
                        </div>
                    </template>
                </div>
            @endif

        </div>
    </div>

</section>
