@extends('layouts.public')

@section('seo_title', __('messages.team.seo_title'))
@section('seo_description', __('messages.team.seo_description'))

@section('content')

<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto py-12 md:py-20">
    <h1 class="font-headline text-[48px] md:text-[64px] font-semibold text-[#1E293B]
               mb-4 tracking-tight leading-none">
        {{ __('messages.team.title') }}
    </h1>
    <p class="text-[20px] text-[#64748B] max-w-xl leading-relaxed font-light">
        {{ __('messages.team.subtitle') }}
    </p>
</section>

<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto pb-32">
    @if(empty($members))
        <p class="text-[#64748B] text-center py-16">{{ __('messages.team.empty') }}</p>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        @foreach($members as $member)
        <div class="group flex flex-col items-center text-center">
            {{-- Avatar --}}
            <div class="w-32 h-32 rounded-full bg-[#e7e8ef] mb-5 overflow-hidden
                        ring-4 ring-white shadow-md group-hover:ring-[#00346f]/20
                        transition-all duration-300">
                @if($member->photoUrl())
                    <img src="{{ $member->photoUrl() }}"
                         alt="{{ $member->name() }}"
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-[#e7e8ef] to-[#c2c6d3]
                                flex items-center justify-center">
                        <span class="material-symbols-outlined text-[#64748B] text-[48px]">person</span>
                    </div>
                @endif
            </div>
            <h2 class="font-headline font-semibold text-[20px] text-[#1E293B] mb-1">
                {{ $member->name() }}
            </h2>
            <p class="text-[15px] text-[#00346f] font-medium mb-3">
                {{ $member->role()->get(app()->getLocale()) }}
            </p>
            @if($member->bio()->get(app()->getLocale()))
            <p class="text-[15px] text-[#64748B] font-light leading-relaxed line-clamp-4">
                {{ $member->bio()->get(app()->getLocale()) }}
            </p>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</section>

@endsection
