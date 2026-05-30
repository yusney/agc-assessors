<section class="w-full max-w-[1280px] mx-auto px-6 md:px-8 pt-20 pb-24 md:pt-28 md:pb-32 grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-20 items-center relative">
    <div class="lg:col-span-6 space-y-8 relative z-10">
        @if($section->localized('eyebrow'))
            <p class="text-[13px] font-semibold uppercase tracking-[0.22em] text-[#00346f]">{{ $section->localized('eyebrow') }}</p>
        @endif

        <h1 class="font-headline text-[48px] lg:text-[72px] text-[#1E293B] leading-[1.05] tracking-tight font-bold">
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

    <div class="lg:col-span-6 mt-12 lg:mt-0 relative h-full min-h-[400px] lg:min-h-[560px]">
        <div class="absolute inset-0 bg-[#f3f3fa] rounded-[2rem] lg:rounded-[2.5rem] rotate-3 scale-105 opacity-50 z-0"></div>
        <div class="absolute inset-0 rounded-[2rem] lg:rounded-[2.5rem] overflow-hidden shadow-2xl z-10 border border-white/50 bg-gradient-to-tr from-[#ededf5] to-[#f9f9ff]">
            @php
                $heroImageUrl = $section->main_image_media_id
                    ? (\Awcodes\Curator\Models\Media::find($section->main_image_media_id)?->url)
                    : $section->image_url;
            @endphp
            @if($heroImageUrl)
                <img src="{{ $heroImageUrl }}" alt="{{ data_get($section->settings, 'image_alt.' . app()->getLocale(), $section->localized('title')) }}" class="w-full h-full object-cover object-center">
            @endif
        </div>
    </div>
</section>
