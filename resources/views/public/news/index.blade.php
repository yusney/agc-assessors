@extends('layouts.public')

@section('seo_title', __('messages.news.seo_title'))
@section('seo_description', __('messages.news.seo_description'))

@section('content')

{{-- Header --}}
<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto py-12 md:py-16">
    <h1 class="font-headline text-[48px] font-semibold text-[#00346f] mb-3 tracking-tight leading-none">
        {{ __('messages.news.title') }}
    </h1>
    <p class="text-[18px] text-[#424751] max-w-2xl leading-relaxed font-light">
        {{ __('messages.news.subtitle') }}
    </p>
</section>

{{-- Filters (sticky) --}}
<div class="w-full sticky top-20 z-40 bg-[#f9f9ff]/90 backdrop-blur-sm border-b border-[#E2E8F0]"
     x-data="{ active: 'all' }">
    <div class="flex flex-wrap items-center gap-8 px-6 md:px-8 max-w-[1280px] mx-auto py-4">
        @foreach(['all' => __('messages.news.filter_all'), 'fiscal' => 'Fiscal', 'laboral' => 'Laboral', 'comptable' => 'Comptable', 'mercantil' => 'Mercantil'] as $key => $label)
        <button @click="active = '{{ $key }}'"
                :class="active === '{{ $key }}' ? 'text-[#00346f] border-b-2 border-[#00346f] font-semibold' : 'text-[#5c5f61] hover:text-[#00346f] border-b-2 border-transparent'"
                class="text-[15px] tracking-[0.02em] py-2 transition-all">
            {{ $label }}
        </button>
        @endforeach
    </div>
</div>

{{-- Grid --}}
<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto pt-12 pb-24">
    @if(empty($news))
        <p class="text-[#64748B] text-center py-16">{{ __('messages.news.empty') }}</p>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
        @foreach($news as $article)
        <article class="flex flex-col group cursor-pointer">
            <a href="{{ url('/actualitat/' . $article->slug()) }}"
               class="relative w-full aspect-video overflow-hidden rounded-lg bg-[#e2e2e9] mb-6 block">
                @if($article->coverUrl())
                    <img src="{{ $article->coverUrl() }}"
                         alt="{{ $article->title()->get(app()->getLocale()) }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-in-out"
                         loading="lazy">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-[#e7e8ef] to-[#d9d9e1]
                                group-hover:scale-105 transition-transform duration-700 ease-in-out"></div>
                @endif
            </a>
            <div class="flex-grow flex flex-col">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-[#00B4D8] text-[13px] uppercase tracking-wider font-semibold font-body">
                        {{ __('messages.nav.news') }}
                    </span>
                    <span class="text-[#E2E8F0]">•</span>
                    <time class="text-[13px] text-[#5c5f61]">
                        {{ $article->publishedAt()?->format('d M Y') }}
                    </time>
                </div>
                <h3 class="font-headline text-[24px] text-[#00346f] font-semibold leading-tight line-clamp-3 mb-3
                           group-hover:text-[#00B4D8] transition-colors duration-300">
                    <a href="{{ url('/actualitat/' . $article->slug()) }}">
                        {{ $article->title()->get(app()->getLocale()) }}
                    </a>
                </h3>
                <p class="text-[16px] text-[#424751] line-clamp-3 mb-4 flex-grow leading-relaxed">
                    {{ $article->excerpt()->get(app()->getLocale()) }}
                </p>
                <div class="mt-auto">
                    <a href="{{ url('/actualitat/' . $article->slug()) }}"
                       class="inline-flex items-center text-[#00346f] font-semibold text-[15px]
                              group-hover:text-[#00B4D8] transition-colors">
                        {{ __('messages.news.read_more') }}
                        <span class="material-symbols-outlined ml-1 text-[18px]
                                     group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </a>
                </div>
            </div>
        </article>
        @endforeach
    </div>

    @if($total > count($news))
    <div class="mt-16 flex justify-center">
        <a href="{{ url('/actualitat?page=' . ($page + 1)) }}"
           class="border border-[#00346f] text-[#00346f] hover:bg-[#00346f] hover:text-white
                  font-medium text-[15px] py-3 px-8 rounded-full transition-colors duration-300">
            {{ __('messages.news.load_more') }}
        </a>
    </div>
    @endif
    @endif
</section>

@endsection
