@extends('layouts.public')

@section('seo_title', $service->name()->get(app()->getLocale()) . ' – AGC Assessors')
@section('seo_description', strip_tags($service->description()->get(app()->getLocale())))

@section('content')

<div class="w-full px-6 md:px-8 max-w-[1280px] mx-auto py-8">
    <a href="{{ route('services.index') }}"
       class="inline-flex items-center text-[#64748B] hover:text-[#00346f] text-[15px]
              transition-colors group">
        <span class="material-symbols-outlined text-[18px] mr-1
                     group-hover:-translate-x-1 transition-transform">arrow_back</span>
        {{ __('messages.services.back') }}
    </a>
</div>

<article class="w-full px-6 md:px-8 max-w-[800px] mx-auto pb-32">
    @if($service->coverUrl())
    <div class="w-full aspect-video rounded-[1.5rem] overflow-hidden mb-10 shadow-md">
        <img src="{{ $service->coverUrl() }}"
             alt="{{ $service->name()->get(app()->getLocale()) }}"
             class="w-full h-full object-cover">
    </div>
    @endif
    <h1 class="font-headline text-[44px] md:text-[56px] font-semibold text-[#1E293B]
               mb-8 tracking-tight leading-[1.05]">
        {{ $service->name()->get(app()->getLocale()) }}
    </h1>

    <div class="prose prose-lg max-w-none
                prose-headings:font-headline prose-headings:text-[#1E293B]
                prose-p:text-[#424751] prose-p:leading-[1.8] prose-p:text-[17px]
                prose-a:text-[#00346f] prose-li:text-[#424751]">
        {!! $service->description()->get(app()->getLocale()) !!}
    </div>

    <div class="mt-16 p-10 bg-[#f3f3fa] rounded-[1.5rem] border border-[#E2E8F0]/50 text-center">
        <h3 class="font-headline text-[28px] font-semibold text-[#1E293B] mb-3">
            {{ __('messages.services.contact_cta') }}
        </h3>
        <p class="text-[#64748B] mb-8 font-light">{{ __('messages.home.hero_subtitle') }}</p>
        <a href="{{ route('contact') }}" class="btn-primary text-[16px] px-10 py-4">
            {{ __('messages.services.contact_cta') }}
            <span class="material-symbols-outlined text-[20px]">arrow_right_alt</span>
        </a>
    </div>
</article>

@endsection
