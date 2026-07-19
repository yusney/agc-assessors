@php
    $ctaLabel = trim($section->localized('cta_label'));
    $ctaUrl = null;
    $ctaCandidate = $section->cta_url;

    if (is_string($ctaCandidate)) {
        $ctaCandidate = trim($ctaCandidate);
        $containsUnsafeCharacters = preg_match('/[\x00-\x1F\x7F\\\\]/', $ctaCandidate) === 1;
        $isInternalPath = str_starts_with($ctaCandidate, '/') && ! str_starts_with($ctaCandidate, '//');

        if (! $containsUnsafeCharacters && $isInternalPath) {
            $ctaUrl = LaravelLocalization::getLocalizedURL(app()->getLocale(), $ctaCandidate, [], false);
        } elseif (! $containsUnsafeCharacters) {
            $externalCandidate = str_starts_with($ctaCandidate, '//')
                ? 'https:' . $ctaCandidate
                : $ctaCandidate;
            $parsedCandidate = parse_url($externalCandidate);
            $scheme = is_array($parsedCandidate) ? strtolower((string) ($parsedCandidate['scheme'] ?? '')) : null;

            if (
                is_array($parsedCandidate)
                && in_array($scheme, ['http', 'https'], true)
                && ! empty($parsedCandidate['host'])
                && filter_var($externalCandidate, FILTER_VALIDATE_URL) !== false
            ) {
                $ctaUrl = $ctaCandidate;
            }
        }
    }
@endphp

<section class="w-full max-w-[1280px] mx-auto px-6 md:px-8 py-12 md:py-16">
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
        @if($section->localized('body') || $section->localized('subtitle') || ($ctaLabel && $ctaUrl))
            <div class="text-[18px] text-[#64748B] font-light leading-relaxed lg:pt-2 space-y-4">
                @if($section->localized('body') || $section->localized('subtitle'))
                    @foreach(array_filter(explode("\n", $section->localized('body') ?: $section->localized('subtitle'))) as $paragraph)
                        <p>{{ trim($paragraph) }}</p>
                    @endforeach
                @endif

                @if($ctaLabel && $ctaUrl)
                    <div class="pt-4">
                        <a href="{{ $ctaUrl }}" class="btn-primary w-full sm:w-auto text-[16px] px-8 py-4">
                            {{ $ctaLabel }}
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_forward</span>
                        </a>
                    </div>
                @endif
            </div>
        @endif

    </div>
</section>
