@extends('layouts.public')

@section('seo_title', $article->seo()->title()->get(app()->getLocale()) ?: $article->title()->get(app()->getLocale()))
@section('seo_description', $article->seo()->description()->get(app()->getLocale()))
@if($article->seo()->canonicalUrl())
    @section('seo_canonical', $article->seo()->canonicalUrl())
@endif

@push('head')
<style>
    @keyframes readProgress { from { width: 0% } to { width: 100% } }
    #reading-bar { animation: readProgress 10s ease-out forwards; }
</style>
@endpush

@section('content')

{{-- Reading progress bar --}}
<div id="reading-bar"
     class="fixed top-0 left-0 h-[3px] bg-[#00346f] z-[60] w-0"
     x-data
     x-init="
        let bar = $el;
        window.addEventListener('scroll', () => {
            let el = document.documentElement;
            let pct = el.scrollTop / (el.scrollHeight - el.clientHeight);
            bar.style.width = Math.min(pct * 100, 100) + '%';
            bar.style.animation = 'none';
        });
     ">
</div>

{{-- Hero image 70vh --}}
<div class="relative w-full h-[70vh] min-h-[500px] bg-[#e7e8ef] mt-0">
    <div class="absolute inset-0 bg-black/40 z-10"></div>
    @if($article->coverUrl())
        <img src="{{ $article->coverUrl() }}"
             alt="{{ $article->title()->get(app()->getLocale()) }}"
             class="absolute inset-0 w-full h-full object-cover">
    @else
        <div class="absolute inset-0 bg-gradient-to-br from-[#e7e8ef] to-[#c2c6d3]"></div>
    @endif
    <div class="absolute bottom-0 left-0 w-full z-20
                bg-gradient-to-t from-[#f9f9ff] to-transparent pt-32 pb-12">
        <div class="max-w-[720px] mx-auto px-6 md:px-0 text-center">
            <div class="flex items-center justify-center gap-2 mb-6">
                <span class="inline-flex items-center px-3 py-1 rounded-full
                             border border-[#00346f]/30 bg-[#f9f9ff]/80 backdrop-blur-sm
                             text-[#00346f] text-[13px] font-semibold uppercase tracking-wider">
                    {{ __('messages.nav.news') }}
                </span>
            </div>
            <h1 class="font-headline text-[36px] md:text-[52px] leading-[1.1] tracking-tight text-[#1E293B] mb-4">
                {{ $article->title()->get(app()->getLocale()) }}
            </h1>
            <div class="flex items-center justify-center gap-2 text-[#5c5f61] text-[13px] uppercase tracking-widest">
                @if($article->publishedAt())
                    <time>{{ $article->publishedAt()->format('d \d\e F, Y') }}</time>
                    <span class="w-1 h-1 rounded-full bg-[#E2E8F0]"></span>
                @endif
                <span>{{ __('messages.news.read_time') }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Article body (720px) --}}
<main class="pb-32">
    <article class="max-w-[720px] mx-auto px-6 md:px-0 pt-12 relative">

        {{-- Floating social (desktop) --}}
        <aside class="hidden xl:flex flex-col gap-4 absolute -left-24 top-0 sticky top-32 pt-4">
            <button class="w-10 h-10 rounded-full bg-white border border-[#E2E8F0]
                           flex items-center justify-center text-[#5c5f61]
                           hover:text-[#00346f] hover:border-[#00346f] hover:-translate-y-1
                           shadow-sm transition-all duration-300 group"
                    title="LinkedIn">
                <span class="material-symbols-outlined text-[20px]">share</span>
            </button>
            <button class="w-10 h-10 rounded-full bg-white border border-[#E2E8F0]
                           flex items-center justify-center text-[#5c5f61]
                           hover:text-[#00346f] hover:border-[#00346f] hover:-translate-y-1
                           shadow-sm transition-all duration-300"
                    title="WhatsApp">
                <span class="material-symbols-outlined text-[20px]">chat</span>
            </button>
            <button class="w-10 h-10 rounded-full bg-white border border-[#E2E8F0]
                           flex items-center justify-center text-[#5c5f61]
                           hover:text-[#00346f] hover:border-[#00346f] hover:-translate-y-1
                           shadow-sm transition-all duration-300"
                    title="Copiar link">
                <span class="material-symbols-outlined text-[20px]">link</span>
            </button>
        </aside>

        {{-- Content --}}
        <div class="prose prose-lg max-w-none
                    prose-headings:font-headline prose-headings:text-[#1E293B]
                    prose-p:text-[#424751] prose-p:leading-[1.8] prose-p:text-[17px]
                    prose-a:text-[#00346f] prose-a:no-underline hover:prose-a:text-[#00B4D8]
                    prose-blockquote:border-l-[3px] prose-blockquote:border-[#00346f]
                    prose-blockquote:text-[#00346f] prose-blockquote:not-italic prose-blockquote:pl-8
                    prose-li:text-[#424751]">
            {!! $article->body()->get(app()->getLocale()) !!}
        </div>

        {{-- Mobile social --}}
        <div class="xl:hidden flex items-center justify-center gap-6 mt-12 pt-8 border-t border-[#E2E8F0]">
            <span class="text-[13px] text-[#5c5f61] uppercase tracking-wider">
                {{ __('messages.news.share') }}
            </span>
            <div class="flex gap-3">
                <button class="w-10 h-10 rounded-full bg-white border border-[#E2E8F0]
                               flex items-center justify-center text-[#5c5f61]
                               hover:text-[#00346f] shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-[20px]">share</span>
                </button>
                <button class="w-10 h-10 rounded-full bg-white border border-[#E2E8F0]
                               flex items-center justify-center text-[#5c5f61]
                               hover:text-[#00346f] shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-[20px]">chat</span>
                </button>
            </div>
        </div>

        {{-- Newsletter box --}}
        <div class="mt-16 bg-[#f3f3fa] border border-[#E2E8F0]/50 rounded-2xl p-8 md:p-12 text-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-[#00346f]/5 rounded-full blur-3xl -mr-10 -mt-10"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-[#00B4D8]/5 rounded-full blur-3xl -ml-10 -mb-10"></div>
            <div class="relative z-10">
                <h3 class="font-headline text-[24px] font-bold text-[#1E293B] mb-2">
                    {{ __('messages.home.newsletter_title') }}
                </h3>
                <p class="text-[16px] text-[#64748B] mb-8 max-w-md mx-auto font-light leading-relaxed">
                    {{ __('messages.home.newsletter_subtitle') }}
                </p>
                <form class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                    <input type="email" required
                           placeholder="{{ __('messages.home.newsletter_placeholder') }}"
                           class="flex-1 rounded-full border-[#c2c6d3] focus:border-[#00346f]
                                  focus:ring-1 focus:ring-[#00346f] text-[16px] py-3 px-6
                                  shadow-sm bg-white text-[#1E293B]">
                    <button type="submit" class="btn-primary px-8 py-3 text-[15px]">
                        {{ __('messages.home.newsletter_cta') }}
                    </button>
                </form>
            </div>
        </div>
    </article>
</main>

@endsection
