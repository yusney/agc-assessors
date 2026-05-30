<section class="w-full max-w-[1280px] mx-auto px-6 md:px-8 py-16 md:py-20">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-start">

        {{-- Col izquierda: eyebrow + título --}}
        <div>
            @if($section->localized('eyebrow'))
                <p class="text-[13px] font-semibold uppercase tracking-[0.22em] text-[#00346f] mb-5">{{ $section->localized('eyebrow') }}</p>
            @endif
            <h2 class="font-headline text-[40px] md:text-[52px] text-[#1E293B] leading-[1.05] tracking-tight font-semibold">
                {{ $section->localized('title') }}
            </h2>
        </div>

        {{-- Col derecha: cuerpo --}}
        @if($section->localized('body') || $section->localized('subtitle'))
            <div class="text-[18px] text-[#64748B] font-light leading-relaxed lg:pt-2 space-y-4">
                @foreach(array_filter(explode("\n", $section->localized('body') ?: $section->localized('subtitle'))) as $paragraph)
                    <p>{{ trim($paragraph) }}</p>
                @endforeach
            </div>
        @endif

    </div>
</section>
