@php
    $locale = app()->getLocale();
    $title  = $section->localized('title');

    $items = collect($section->setting('testimonials', []))
        ->take((int) $section->setting('limit', 3));
@endphp

@if($items->isNotEmpty())
<section class="w-full py-14 md:py-20 bg-[#F8FAFC]">
    <div class="max-w-[1280px] mx-auto px-6 md:px-8">

        {{-- Section header --}}
        <div class="text-center mb-12 md:mb-16">
            <p class="text-[13px] font-semibold uppercase tracking-[0.22em] text-[#00B4D8] mb-4">
                {{ $locale === 'ca' ? 'Testimonis' : ($locale === 'es' ? 'Testimonios' : 'Testimonials') }}
            </p>
            @if($title)
            <h2 class="font-headline text-[36px] md:text-[48px] text-[#1E293B] leading-[1.05] tracking-tight font-semibold">
                {{ $title }}
            </h2>
            @endif
        </div>

        {{-- Testimonial cards grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
            @foreach($items as $t)
                @php
                    $initials = $t['initials'] ?? strtoupper(substr($t['name'] ?? '?', 0, 1));
                @endphp
                <article class="flex flex-col bg-white rounded-2xl shadow-sm border border-[#E2E8F0]/70 p-8 hover:shadow-md transition-shadow duration-200">

                    {{-- Stars --}}
                    <div class="flex gap-1 mb-6" aria-label="5 estrelles" role="img">
                        @for($i = 0; $i < 5; $i++)
                            <span class="material-symbols-outlined text-[#F59E0B] text-[20px]" aria-hidden="true"
                                  style="font-variation-settings: 'FILL' 1">star</span>
                        @endfor
                    </div>

                    {{-- Quote --}}
                    <blockquote class="flex-1 text-[17px] text-[#475569] leading-relaxed font-light mb-8">
                        {{ $t['text'] ?? '' }}
                    </blockquote>

                    {{-- Person --}}
                    <footer class="flex items-center gap-4">
                        <div class="w-11 h-11 rounded-full bg-[#00346f] flex items-center justify-center shrink-0" aria-hidden="true">
                            <span class="text-white text-[13px] font-semibold tracking-wide">{{ $initials }}</span>
                        </div>
                        <div>
                            <p class="text-[15px] font-semibold text-[#1E293B]">{{ $t['name'] ?? '' }}</p>
                            <p class="text-[13px] text-[#64748B]">
                                {{ $t['role'] ?? '' }}
                                @if(!empty($t['company']))
                                    <span class="mx-1 text-[#CBD5E1]">·</span>
                                    {{ $t['company'] }}
                                @endif
                            </p>
                        </div>
                    </footer>

                </article>
            @endforeach
        </div>

    </div>
</section>
@endif
