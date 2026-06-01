@extends('layouts.public')

@section('seo_title', $service->name()->get(app()->getLocale()) . ' – AGC Assessors')
@section('seo_description', strip_tags($service->description()->get(app()->getLocale())))

@push('styles')
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(24px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.8s ease-out forwards;
        opacity: 0;
    }
    .animate-fade-in {
        animation: fadeIn 0.6s ease-out forwards;
        opacity: 0;
    }
    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
    .delay-300 { animation-delay: 0.3s; }
    .delay-400 { animation-delay: 0.4s; }
</style>
@endpush

@section('content')

{{-- HERO SECTION --}}
<section class="w-full bg-[#f9f9ff] border-b border-[#E2E8F0]">
    <div class="w-full max-w-[1280px] mx-auto px-6 md:px-8 py-16 md:py-24">
        {{-- Breadcrumb --}}
        <a href="{{ route('services.index') }}"
           class="inline-flex items-center text-[#64748B] hover:text-[#00346f] text-[14px]
                  transition-colors group mb-10 animate-fade-in">
            <span class="material-symbols-outlined text-[18px] mr-1.5
                         group-hover:-translate-x-1 transition-transform">arrow_back</span>
            {{ __('messages.services.back') }}
        </a>

        <div class="grid grid-cols-1 {{ $service->coverUrl() ? 'lg:grid-cols-2' : '' }} gap-12 lg:gap-20 items-center">
            {{-- Text --}}
            <div class="order-2 lg:order-1 {{ $service->coverUrl() ? '' : 'lg:col-span-2 max-w-[720px] mx-auto text-center' }}">
                <span class="inline-block text-[#00346f] text-[13px] font-semibold uppercase tracking-[0.22em] mb-5
                             animate-fade-in-up delay-100">
                    {{ __('messages.services.title') }}
                </span>

                <h1 class="font-headline text-[36px] md:text-[48px] lg:text-[56px] text-[#1E293B]
                           mb-6 leading-[1.05] tracking-tight font-bold
                           animate-fade-in-up delay-200">
                    {{ $service->name()->get(app()->getLocale()) }}
                </h1>

                @php
                    $desc = $service->description()->get(app()->getLocale());
                    $excerpt = Str::limit(strip_tags($desc), 200);
                @endphp

                @if($excerpt)
                <p class="text-[#64748B] text-[18px] md:text-[20px] leading-relaxed font-light max-w-[480px] mb-8
                          animate-fade-in-up delay-300">
                    {{ $excerpt }}
                </p>
                @endif

                <a href="#content" class="inline-flex items-center gap-2 text-[#00346f] font-medium text-[15px]
                   hover:gap-3 transition-all animate-fade-in-up delay-400">
                    {{ __('messages.services.cta') }}
                    <span class="material-symbols-outlined text-[18px]">arrow_downward</span>
                </a>
            </div>

            {{-- Image with animation --}}
            @if($service->coverUrl())
            <div class="order-1 lg:order-2 animate-fade-in-up delay-300">
                <div class="relative group">
                    <div class="absolute inset-0 bg-[#00346f]/5 rounded-[2rem] rotate-2 scale-105
                                transition-transform duration-500 group-hover:rotate-3"></div>
                    <div class="relative rounded-[2rem] overflow-hidden shadow-xl border border-[#E2E8F0]
                                transition-all duration-500 group-hover:shadow-2xl group-hover:-translate-y-1">
                        <img src="{{ $service->coverUrl() }}"
                             alt="{{ $service->name()->get(app()->getLocale()) }}"
                             class="w-full aspect-[4/3] object-cover
                                    transition-transform duration-700 group-hover:scale-105">
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>

{{-- CONTENT SECTION --}}
<section class="w-full bg-white" id="content">
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
<section class="w-full bg-white border-t border-[#E2E8F0]">
    <div class="w-full max-w-[1280px] mx-auto px-6 md:px-8 py-20 md:py-24">
        <div class="max-w-[800px] mx-auto text-center">
            <h2 class="font-headline text-[32px] md:text-[40px] text-[#1E293B] mb-4
                       leading-tight font-semibold tracking-tight">
                {{ __('messages.services.contact_cta') }}
            </h2>
            <p class="text-[#64748B] text-[18px] leading-relaxed font-light mb-10 max-w-[560px] mx-auto">
                {{ __('messages.home.hero_subtitle') }}
            </p>
            <a href="{{ route('contact') }}"
               class="inline-flex items-center gap-2 bg-[#00346f] text-white
                      px-10 py-4 rounded-xl font-semibold text-[16px]
                      hover:bg-[#004a99]
                      transition-all duration-300 shadow-lg shadow-[#00346f]/10">
                {{ __('messages.services.contact_cta') }}
                <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
            </a>
        </div>
    </div>
</section>

@endsection
