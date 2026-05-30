@extends('layouts.public')

@section('seo_title', __('messages.services.seo_title'))
@section('seo_description', __('messages.services.seo_description'))

@section('content')

{{-- Header --}}
<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto py-12 md:py-20">
    <h1 class="font-headline text-[48px] md:text-[64px] font-semibold text-[#1E293B]
               mb-4 tracking-tight leading-none">
        {{ __('messages.services.title') }}
    </h1>
    <p class="text-[20px] text-[#64748B] max-w-2xl leading-relaxed font-light">
        {{ __('messages.services.subtitle') }}
    </p>
</section>

{{-- Grid --}}
<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto pb-32">
    @if(empty($services))
        <p class="text-[#64748B] text-center py-16">{{ __('messages.services.empty') }}</p>
    @else

    @php
    $icons = ['balance', 'work_outline', 'monitoring', 'gavel', 'real_estate_agent', 'groups',
              'receipt_long', 'account_balance', 'trending_up', 'handshake', 'security', 'diversity_3'];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($services as $i => $service)
        <a href="{{ route('services.show', $service->slug()) }}"
           class="group flex flex-col rounded-[1.5rem] border border-[#E2E8F0]
                  bg-white hover:bg-[#f3f3fa] hover:border-[#00346f]/20
                  p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
            @if($service->coverUrl())
                <div class="mb-6 w-full aspect-video rounded-xl overflow-hidden">
                    <img src="{{ $service->coverUrl() }}"
                         alt="{{ $service->name()->get(app()->getLocale()) }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                         loading="lazy">
                </div>
            @else
            <div class="mb-6 w-14 h-14 rounded-2xl bg-[#f3f3fa] group-hover:bg-white
                        flex items-center justify-center transition-colors duration-300">
                <span class="material-symbols-outlined text-[#1E293B] group-hover:text-[#00346f]
                             transition-colors text-[32px]"
                      style="font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 32">
                    {{ $icons[$i % count($icons)] }}
                </span>
            </div>
            @endif
            <h2 class="font-headline font-semibold text-[22px] text-[#1E293B] mb-3 leading-tight">
                {{ $service->name()->get(app()->getLocale()) }}
            </h2>
            <p class="text-[16px] text-[#64748B] line-clamp-4 mb-6 flex-grow font-light leading-relaxed">
                {!! strip_tags($service->description()->get(app()->getLocale())) !!}
            </p>
            <div class="flex items-center text-[#1E293B] font-medium text-[15px]
                        group-hover:text-[#00346f] transition-colors mt-auto pt-4
                        border-t border-[#E2E8F0] group-hover:border-[#00346f]/20">
                {{ __('messages.services.cta') }}
                <span class="material-symbols-outlined ml-1.5 text-[18px]
                             group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </div>
        </a>
        @endforeach
    </div>

    @endif
</section>

@endsection
