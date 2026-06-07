@extends('layouts.public')

@section('seo_title', $settings['seo_title'][$locale] ?? __('messages.careers.seo_title'))
@section('seo_description', $settings['seo_description'][$locale] ?? __('messages.careers.seo_description'))

@section('content')

{{-- ── Hero ──────────────────────────────────────────────────────────── --}}
<section class="relative bg-white pt-32 pb-20 overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            {{-- Text --}}
            <div>
                <h1 class="font-headline text-[42px] md:text-[56px] leading-[1.1] tracking-tight text-[#1E293B] mb-6">
                    {!! $settings['hero_title'][$locale] ?? __('messages.careers.page_title') !!}
                </h1>
                @if(!empty($settings['hero_subtitle'][$locale]))
                    <p class="text-[18px] text-[#424751] leading-[1.7] mb-8">
                        {{ $settings['hero_subtitle'][$locale] }}
                    </p>
                @endif
                @php
                    $heroCtaText = $settings['hero_cta_text'][$locale] ?? __('messages.careers.hero_cta');
                    $heroCtaUrl  = $settings['hero_cta_url'] ?? '#application-form';
                @endphp
                <a href="{{ $heroCtaUrl }}"
                   class="inline-flex items-center gap-2 bg-[#00346f] text-white font-semibold px-8 py-4 rounded-xl hover:bg-[#00285a] transition-colors duration-200">
                    {{ $heroCtaText }}
                    <span class="material-symbols-outlined text-[20px]">&#xe5c8;</span>
                </a>
            </div>

            {{-- Image --}}
            <div class="relative">
                @php
                    $heroImageUrl = null;
                    if (!empty($settings['hero_image_media_id'])) {
                        $media = \Awcodes\Curator\Models\Media::find($settings['hero_image_media_id']);
                        $heroImageUrl = $media?->url;
                    }
                    $heroImageUrl ??= $settings['hero_image_url'] ?? null;
                @endphp
                @if($heroImageUrl)
                    <img src="{{ $heroImageUrl }}"
                         alt="{{ $settings['hero_title'][$locale] ?? __('messages.careers.page_title') }}"
                         class="w-full rounded-3xl shadow-xl object-cover aspect-[4/3]">
                @else
                    <div class="w-full rounded-3xl bg-gradient-to-br from-[#00346f]/10 to-[#00B4D8]/20 aspect-[4/3] flex items-center justify-center">
                        <span class="material-symbols-outlined text-[80px] text-[#00346f]/30">work_outline</span>
                    </div>
                @endif
            </div>

        </div>
    </div>
</section>

{{-- ── Benefits ──────────────────────────────────────────────────────── --}}
@if(!empty($settings['benefits']) && count($settings['benefits']) > 0)
<section class="py-20 bg-[#F8FAFC]">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <h2 class="font-headline text-[32px] md:text-[40px] text-center text-[#1E293B] mb-14">
            {{ __('messages.careers.benefits_title') }}
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($settings['benefits'] as $benefit)
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-[#E2E8F0] hover:shadow-md transition-shadow duration-200">
                    @if(!empty($benefit['icon']))
                        <div class="w-14 h-14 bg-[#00346f]/10 rounded-xl flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined text-[28px] text-[#00346f]">{{ $benefit['icon'] }}</span>
                        </div>
                    @endif
                    <h3 class="font-headline text-[20px] font-semibold text-[#1E293B] mb-3">
                        {{ $benefit['title'][$locale] ?? ($benefit['title']['ca'] ?? '') }}
                    </h3>
                    @if(!empty($benefit['description'][$locale] ?? $benefit['description']['ca'] ?? null))
                        <p class="text-[#424751] leading-[1.7] text-[15px]">
                            {{ $benefit['description'][$locale] ?? ($benefit['description']['ca'] ?? '') }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── Application Form ─────────────────────────────────────────────── --}}
<section id="application-form" class="py-20 bg-white">
    <div class="max-w-3xl mx-auto px-6 lg:px-8">
        @include('public.components.job-application-form', ['settings' => $settings, 'locale' => $locale])
    </div>
</section>

{{-- ── Footer CTA ───────────────────────────────────────────────────── --}}
@if(!empty($settings['footer_cta_title'][$locale] ?? null))
<section class="py-20 bg-[#00346f]">
    <div class="max-w-4xl mx-auto px-6 lg:px-8 text-center">
        <h2 class="font-headline text-[32px] md:text-[40px] text-white mb-8">
            {{ $settings['footer_cta_title'][$locale] ?? __('messages.careers.footer_cta') }}
        </h2>
        <a href="#application-form"
           class="inline-flex items-center gap-2 bg-white text-[#00346f] font-semibold px-8 py-4 rounded-xl hover:bg-[#f0f6ff] transition-colors duration-200">
            {{ $settings['footer_cta_button_text'][$locale] ?? __('messages.careers.footer_cta_button') }}
            <span class="material-symbols-outlined text-[20px]">arrow_downward</span>
        </a>
    </div>
</section>
@endif

@endsection
