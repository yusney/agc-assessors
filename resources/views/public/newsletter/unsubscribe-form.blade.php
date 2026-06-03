@extends('layouts.public')

@section('seo_title', __('messages.newsletter.unsubscribe_form_title') . ' - AGC Assessors')

@section('content')

<main class="pt-32 pb-32">
    <article class="max-w-[520px] mx-auto px-6 md:px-0">

        <h1 class="font-headline text-[32px] md:text-[40px] leading-[1.15] tracking-tight text-[#1E293B] mb-4 text-center">
            {{ __('messages.newsletter.unsubscribe_form_title') }}
        </h1>

        <p class="text-[16px] text-[#64748B] leading-relaxed mb-10 text-center">
            {{ __('messages.newsletter.unsubscribe_form_subtitle') }}
        </p>

        <form action="{{ route('newsletter.unsubscribe.process') }}" method="POST" class="flex flex-col gap-5">
            @csrf
            <x-spam-protection />

            <div>
                <label for="email" class="block text-sm font-medium text-[#475569] mb-1.5">
                    {{ __('messages.newsletter.unsubscribe_form_email') }}
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    value="{{ old('email') }}"
                    class="w-full h-[52px] px-5 rounded-[12px] border border-[#CBD5E1] bg-white text-[15px] text-[#1E293B] placeholder:text-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-[#00346f]/20 focus:border-[#00346f] transition-all"
                    placeholder="{{ __('messages.newsletter.unsubscribe_form_placeholder') }}"
                >
                @error('email')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary text-[16px] px-8 py-4 w-full">
                {{ __('messages.newsletter.unsubscribe_form_submit') }}
            </button>
        </form>

    </article>
</main>

@endsection
