@extends('layouts.public')

@section('seo_title', __('messages.newsletter.unsubscribe_title') . ' - AGC Assessors')

@section('content')

<main class="pt-32 pb-32">
    <article class="max-w-[720px] mx-auto px-6 md:px-0 text-center">

        <div class="w-20 h-20 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-8">
            <span class="material-symbols-outlined text-green-600 text-[40px]">check_circle</span>
        </div>

        <h1 class="font-headline text-[36px] md:text-[48px] leading-[1.15] tracking-tight text-[#1E293B] mb-6">
            {{ __('messages.newsletter.unsubscribe_title') }}
        </h1>

        <p class="text-[18px] text-[#64748B] leading-relaxed mb-10 max-w-lg mx-auto">
            {{ __('messages.newsletter.unsubscribe_message') }}
        </p>

        <a href="{{ LaravelLocalization::getLocalizedURL(app()->getLocale(), '/') }}" class="btn-primary text-[16px] px-8 py-4">
            {{ __('messages.newsletter.unsubscribe_back') }}
        </a>

    </article>
</main>

@endsection
