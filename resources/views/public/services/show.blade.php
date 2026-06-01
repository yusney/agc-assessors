@extends('layouts.public')

@section('seo_title', $service->name()->get(app()->getLocale()) . ' – AGC Assessors')
@section('seo_description', strip_tags($service->description()->get(app()->getLocale())))

@section('content')

{{-- HERO SECTION --}}
<section class="relative w-full min-h-[500px] md:min-h-[600px] flex items-center overflow-hidden bg-[#00346f]">
    @if($service->coverUrl())
    <div class="absolute inset-0 z-0">
        <img src="{{ $service->coverUrl() }}"
             alt="{{ $service->name()->get(app()->getLocale()) }}"
             class="w-full h-full object-cover opacity-30 mix-blend-overlay">
        <div class="absolute inset-0 bg-gradient-to-r from-[#00346f]/90 to-[#00346f]/60"></div>
    </div>
    @endif

    <div class="w-full max-w-[1280px] mx-auto px-6 md:px-8 relative z-10 py-20">
        {{-- Breadcrumb --}}
        <a href="{{ route('services.index') }}"
           class="inline-flex items-center text-white/70 hover:text-white text-[14px]
                  transition-colors group mb-8">
            <span class="material-symbols-outlined text-[18px] mr-1.5
                         group-hover:-translate-x-1 transition-transform">arrow_back</span>
            {{ __('messages.services.back') }}
        </a>

        <div class="max-w-[700px]">
            <span class="inline-block bg-[#00B4D8]/20 text-white px-3 py-1.5 rounded-lg mb-6
                         text-[13px] font-medium tracking-wider uppercase">
                {{ __('messages.services.title') }}
            </span>

            <h1 class="font-headline text-[40px] md:text-[56px] lg:text-[64px] text-white
                       mb-6 leading-[1.05] tracking-tight font-bold">
                {{ $service->name()->get(app()->getLocale()) }}
            </h1>

            @php
                $desc = $service->description()->get(app()->getLocale());
                $excerpt = Str::limit(strip_tags($desc), 180);
            @endphp

            @if($excerpt)
            <p class="text-white/80 text-[18px] md:text-[20px] leading-relaxed font-light max-w-[540px]">
                {{ $excerpt }}
            </p>
            @endif
        </div>
    </div>
</section>

{{-- CONTENT SECTION --}}
<section class="w-full bg-white">
    <article class="max-w-[720px] mx-auto px-6 md:px-8 py-16 md:py-24">

        {{-- Rich description --}}
        <div class="prose prose-lg max-w-none
                    prose-headings:font-headline prose-headings:text-[#1E293B]
                    prose-headings:font-semibold prose-headings:tracking-tight
                    prose-h2:text-[28px] prose-h2:mt-12 prose-h2:mb-6
                    prose-h3:text-[22px] prose-h3:mt-10 prose-h3:mb-4
                    prose-p:text-[#424751] prose-p:leading-[1.8] prose-p:text-[17px] prose-p:mb-6
                    prose-a:text-[#00346f] prose-a:font-medium prose-a:no-underline
                    prose-a:hover:underline prose-a:underline-offset-4
                    prose-strong:text-[#1E293B] prose-strong:font-semibold
                    prose-ul:my-6 prose-ul:space-y-3
                    prose-li:text-[#424751] prose-li:text-[17px] prose-li:marker:text-[#00B4D8]
                    prose-blockquote:border-l-4 prose-blockquote:border-[#00346f]
                    prose-blockquote:bg-[#f3f3fa] prose-blockquote:pl-6 prose-blockquote:py-4
                    prose-blockquote:pr-6 prose-blockquote:rounded-r-xl
                    prose-blockquote:text-[#1E293B] prose-blockquote:font-medium
                    prose-blockquote:italic prose-blockquote:my-10
                    prose-img:rounded-xl prose-img:shadow-md">
            {!! $desc !!}
        </div>

    </article>
</section>

{{-- CTA SECTION --}}
<section class="w-full bg-[#00346f] relative overflow-hidden">
    {{-- Decorative elements --}}
    <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-[#00B4D8] opacity-20 rounded-full blur-[100px]"></div>
    <div class="absolute -left-20 -top-20 w-80 h-80 bg-[#004a99] opacity-40 rounded-full blur-[100px]"></div>

    <div class="w-full max-w-[1280px] mx-auto px-6 md:px-8 py-20 md:py-24 relative z-10">
        <div class="flex flex-col md:flex-row items-center justify-between gap-10">
            <div class="max-w-[600px] text-center md:text-left">
                <h2 class="font-headline text-[32px] md:text-[40px] text-white mb-4
                           leading-tight font-semibold">
                    {{ __('messages.services.contact_cta') }}
                </h2>
                <p class="text-white/70 text-[18px] leading-relaxed font-light">
                    {{ __('messages.home.hero_subtitle') }}
                </p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('contact') }}"
                   class="inline-flex items-center gap-2 bg-[#00B4D8] text-white
                          px-8 py-4 rounded-xl font-semibold text-[16px]
                          hover:bg-white hover:text-[#00346f]
                          transition-all duration-300 shadow-xl">
                    {{ __('messages.services.contact_cta') }}
                    <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
