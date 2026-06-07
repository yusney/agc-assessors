@php
    $items = collect($section->carousel_items ?? [])
        ->map(function (array $item): array {
            $item['_resolved_image'] = data_get($item, 'media_id')
                ? (\Awcodes\Curator\Models\Media::find((int) $item['media_id'])?->url)
                : data_get($item, 'image_url');
            return $item;
        })
        ->filter(fn (array $item): bool => filled($item['_resolved_image']))
        ->values();
@endphp

@if($items->isNotEmpty())
<section class="w-full max-w-[1280px] mx-auto px-6 md:px-8 py-16 md:py-20">
    <div class="mb-10 max-w-3xl">
        @if($section->localized('eyebrow'))
            <p class="text-[13px] font-semibold uppercase tracking-[0.22em] text-[#00346f] mb-4">{{ $section->localized('eyebrow') }}</p>
        @endif
        <h2 class="font-headline text-[36px] md:text-[48px] text-[#1E293B] mb-5 tracking-tight font-semibold leading-none">
            {{ $section->localized('title') }}
        </h2>
        @if($section->localized('subtitle'))
            <p class="text-[20px] text-[#64748B] font-light leading-relaxed">{{ $section->localized('subtitle') }}</p>
        @endif
    </div>

    <div x-data="{ active: 0, count: {{ $items->count() }} }" class="relative rounded-[2.5rem] overflow-hidden bg-[#1E293B] min-h-[520px] shadow-2xl border border-white/60">
        @foreach($items as $index => $item)
            @php
                $title = data_get($item, 'title.' . app()->getLocale(), data_get($item, 'title.ca', ''));
                $eyebrow = data_get($item, 'eyebrow.' . app()->getLocale(), data_get($item, 'eyebrow.ca', ''));
                $body = data_get($item, 'body.' . app()->getLocale(), data_get($item, 'body.ca', ''));
                $ctaLabel = data_get($item, 'cta_label.' . app()->getLocale(), data_get($item, 'cta_label.ca', ''));
            @endphp
            <article x-show="active === {{ $index }}" x-transition.opacity.duration.500ms class="absolute inset-0">
                <img src="{{ $item['_resolved_image'] }}" alt="{{ $title }}" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-[#1E293B]/90 via-[#1E293B]/55 to-[#00346f]/10"></div>
                <div class="relative z-10 h-full min-h-[520px] flex items-end md:items-center p-8 md:p-14 lg:p-20">
                    <div class="max-w-2xl text-white">
                        @if($eyebrow)
                            <p class="text-[13px] font-semibold uppercase tracking-[0.22em] text-[#abc7ff] mb-4">{{ $eyebrow }}</p>
                        @endif
                        @if($title)
                            <h3 class="font-headline text-[40px] md:text-[58px] leading-[1.02] tracking-tight font-semibold mb-5">{{ $title }}</h3>
                        @endif
                        @if($body)
                            <p class="text-[18px] md:text-[20px] text-white/80 leading-relaxed font-light mb-8">{{ $body }}</p>
                        @endif
                        @if($ctaLabel && data_get($item, 'cta_url'))
                            <a href="{{ url(data_get($item, 'cta_url')) }}" class="inline-flex items-center justify-center gap-2 bg-white text-[#1E293B] font-medium px-7 py-3 rounded-full hover:bg-[#d7e2ff] transition-colors duration-300">
                                {{ $ctaLabel }}
                                <span class="material-symbols-outlined text-[20px]">&#xe941;</span>
                            </a>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach

        @if($items->count() > 1)
            <div class="absolute bottom-6 right-6 z-20 flex items-center gap-3">
                <button type="button" x-on:click="active = active === 0 ? count - 1 : active - 1" class="w-11 h-11 rounded-full bg-white/90 text-[#1E293B] hover:bg-white transition-colors flex items-center justify-center">
                    <span class="material-symbols-outlined">chevron_left</span>
                    <span class="sr-only">Previous slide</span>
                </button>
                <button type="button" x-on:click="active = active === count - 1 ? 0 : active + 1" class="w-11 h-11 rounded-full bg-white/90 text-[#1E293B] hover:bg-white transition-colors flex items-center justify-center">
                    <span class="material-symbols-outlined">chevron_right</span>
                    <span class="sr-only">Next slide</span>
                </button>
            </div>
        @endif
    </div>
</section>
@endif
