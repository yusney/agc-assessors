@php
    use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
    
    $badges = collect(SiteSetting::get('trust_bar'))
        ?->filter(fn ($b) => !empty($b['is_active']))
        ->values();

    // Fallback: default badges until admin saves
    if ($badges === null || $badges->isEmpty()) {
        $badges = collect([
            [
                'icon'        => 'verified',
                'title_ca'    => 'UNE 420001',
                'title_es'    => 'UNE 420001',
                'title_en'    => 'UNE 420001',
                'subtitle_ca' => 'Qualitat certificada',
                'subtitle_es' => 'Calidad certificada',
                'subtitle_en' => 'Certified quality',
                'url'         => '',
            ],
            [
                'icon'        => 'history',
                'title_ca'    => '+25 anys',
                'title_es'    => '+25 años',
                'title_en'    => '+25 years',
                'subtitle_ca' => "d'experiència professional",
                'subtitle_es' => 'de experiencia profesional',
                'subtitle_en' => 'of professional experience',
                'url'         => '',
            ],
        ]);
    }
    $locale = app()->getLocale();
@endphp

@if($badges->isNotEmpty())
<section class="w-full bg-white border-y border-[#E2E8F0]">
    <div class="max-w-[1280px] mx-auto px-6 md:px-8 py-8 md:py-10">

        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-center gap-6 md:gap-20 max-w-[360px] md:max-w-none mx-auto">

            @foreach($badges as $index => $badge)
                @php
                    $icon         = $badge['icon'] ?? 'check_circle';
                    $imageMediaId = $badge['image_media_id'] ?? null;
                    $imageUrl     = null;
                    if ($imageMediaId) {
                        $imageUrl = \Awcodes\Curator\Models\Media::find($imageMediaId)?->url;
                    }
                    $title       = $badge['title_' . $locale] ?? $badge['title_ca'] ?? '';
                    $subtitle    = $badge['subtitle_' . $locale] ?? $badge['subtitle_ca'] ?? '';
                    $url         = $badge['url'] ?? '';
                    $hasUrl      = !empty($url);
                @endphp

                @if($index > 0)
                    <span class="block md:hidden h-px w-full bg-[#E2E8F0]"></span>
                    <span class="hidden md:block w-px h-12 bg-[#E2E8F0]"></span>
                @endif

                @if($hasUrl)
                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                   class="grid grid-cols-[3.5rem_1fr] items-center gap-4 group hover:opacity-80 transition-opacity text-left">
                @else
                <div class="grid grid-cols-[3.5rem_1fr] items-center gap-4 group text-left">
                @endif

                    <div class="w-14 h-14 rounded-2xl bg-[#00346f]/8 flex items-center justify-center
                                group-hover:bg-[#00346f]/12 transition-colors duration-300 flex-shrink-0 overflow-hidden">
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $title }}" class="w-10 h-10 object-contain">
                        @else
                            <span class="material-symbols-outlined text-[#00346f] text-[28px]">{{ $icon }}</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-[14px] font-semibold text-[#0f172a] leading-tight">{{ $title }}</p>
                        @if($subtitle)
                        <p class="text-[13px] text-[#64748B]">{{ $subtitle }}</p>
                        @endif
                    </div>

                @if($hasUrl)
                </a>
                @else
                </div>
                @endif

            @endforeach

        </div>

    </div>
</section>
@endif
