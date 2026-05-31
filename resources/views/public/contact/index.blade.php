@extends('layouts.public')

@section('seo_title', __('messages.contact.seo_title'))
@section('seo_description', __('messages.contact.seo_description'))

@section('content')

<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto py-12 md:py-20
                grid grid-cols-1 lg:grid-cols-12 gap-16 lg:gap-24 items-start">

    {{-- Left: info --}}
    <div class="lg:col-span-5">
        <h1 class="font-headline text-[48px] md:text-[60px] font-semibold text-[#1E293B]
                   mb-4 tracking-tight leading-[1.05]">
            {{ __('messages.contact.title') }}
        </h1>
        <p class="text-[20px] text-[#64748B] mb-12 font-light leading-relaxed">
            {{ __('messages.contact.subtitle') }}
        </p>

        <ul class="space-y-7">
            <li class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-[#f3f3fa] flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[#00346f] text-[24px]">location_on</span>
                </div>
                <div>
                    <p class="text-[14px] text-[#64748B] font-medium uppercase tracking-wider mb-1">{{ __('messages.contact.address_label') }}</p>
                    <p class="text-[17px] text-[#1E293B]">{{ __('messages.contact.address') }}</p>
                </div>
            </li>
            <li class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-[#f3f3fa] flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[#00346f] text-[24px]">phone</span>
                </div>
                <div>
                    <p class="text-[14px] text-[#64748B] font-medium uppercase tracking-wider mb-1">{{ __('messages.contact.phone_label') }}</p>
                    <a href="tel:{{ __('messages.contact.phone_value') }}"
                       class="text-[17px] text-[#1E293B] hover:text-[#00346f] transition-colors">
                        {{ __('messages.contact.phone_value') }}
                    </a>
                </div>
            </li>
            <li class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-[#f3f3fa] flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[#00346f] text-[24px]">mail</span>
                </div>
                <div>
                    <p class="text-[14px] text-[#64748B] font-medium uppercase tracking-wider mb-1">{{ __('messages.contact.email_label') }}</p>
                    <a href="mailto:{{ __('messages.contact.email_value') }}"
                       class="text-[17px] text-[#1E293B] hover:text-[#00346f] transition-colors">
                        {{ __('messages.contact.email_value') }}
                    </a>
                </div>
            </li>
            <li class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-[#f3f3fa] flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[#00346f] text-[24px]">schedule</span>
                </div>
                <div>
                    <p class="text-[14px] text-[#64748B] font-medium uppercase tracking-wider mb-1">{{ __('messages.contact.hours_label') }}</p>
                    <p class="text-[17px] text-[#1E293B]">{{ __('messages.contact.hours_value') }}</p>
                </div>
            </li>
        </ul>
    </div>

    {{-- Right: form --}}
    <div class="lg:col-span-7">
        @if(session('success'))
        <div class="mb-8 p-6 rounded-2xl bg-green-50 border border-green-200 flex items-center gap-4">
            <span class="material-symbols-outlined text-green-600 text-[28px]">check_circle</span>
            <p class="text-green-800 font-medium">{{ __('messages.contact.success') }}</p>
        </div>
        @endif

        <form action="{{ route('contact.store') }}" method="POST"
              class="bg-white rounded-[2rem] border border-[#E2E8F0] p-8 md:p-12 shadow-sm space-y-6"
              x-data="{ accepted: false }">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[14px] font-medium text-[#1E293B] mb-2">
                        {{ __('messages.contact.name') }} *
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full rounded-xl border border-[#c2c6d3] px-4 py-3 text-[16px]
                                  text-[#1E293B] placeholder:text-[#64748B]/50
                                  focus:outline-none focus:ring-1 focus:ring-[#00346f] focus:border-[#00346f]
                                  @error('name') border-red-400 @enderror">
                    @error('name')<p class="mt-1 text-[13px] text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-[14px] font-medium text-[#1E293B] mb-2">
                        {{ __('messages.contact.email') }} *
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full rounded-xl border border-[#c2c6d3] px-4 py-3 text-[16px]
                                  text-[#1E293B] placeholder:text-[#64748B]/50
                                  focus:outline-none focus:ring-1 focus:ring-[#00346f] focus:border-[#00346f]
                                  @error('email') border-red-400 @enderror">
                    @error('email')<p class="mt-1 text-[13px] text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[14px] font-medium text-[#1E293B] mb-2">
                        {{ __('messages.contact.phone') }}
                    </label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                           class="w-full rounded-xl border border-[#c2c6d3] px-4 py-3 text-[16px]
                                  text-[#1E293B] placeholder:text-[#64748B]/50
                                  focus:outline-none focus:ring-1 focus:ring-[#00346f] focus:border-[#00346f]">
                </div>
                <div>
                    <label class="block text-[14px] font-medium text-[#1E293B] mb-2">
                        {{ __('messages.contact.subject') }} *
                    </label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required
                           class="w-full rounded-xl border border-[#c2c6d3] px-4 py-3 text-[16px]
                                  text-[#1E293B] placeholder:text-[#64748B]/50
                                  focus:outline-none focus:ring-1 focus:ring-[#00346f] focus:border-[#00346f]
                                  @error('subject') border-red-400 @enderror">
                    @error('subject')<p class="mt-1 text-[13px] text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-[14px] font-medium text-[#1E293B] mb-2">
                    {{ __('messages.contact.message') }} *
                </label>
                <textarea name="message" rows="6" required
                          class="w-full rounded-xl border border-[#c2c6d3] px-4 py-3 text-[16px]
                                 text-[#1E293B] placeholder:text-[#64748B]/50 resize-none
                                 focus:outline-none focus:ring-1 focus:ring-[#00346f] focus:border-[#00346f]
                                 @error('message') border-red-400 @enderror">{{ old('message') }}</textarea>
                @error('message')<p class="mt-1 text-[13px] text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-start gap-3">
                <input type="checkbox" name="privacy" id="privacy" value="1"
                       x-model="accepted"
                       class="mt-0.5 w-4 h-4 rounded border-[#c2c6d3]
                              accent-[#00346f] cursor-pointer
                              @error('privacy') border-red-400 @enderror">
                <label for="privacy" class="text-[15px] text-[#64748B] cursor-pointer leading-relaxed">
                    {!! __('messages.contact.privacy', ['url' => LaravelLocalization::getLocalizedURL(app()->getLocale(), '/pages/privacy-policy')]) !!}
                </label>
            </div>
            @error('privacy')<p class="-mt-3 text-[13px] text-red-500">{{ $message }}</p>@enderror

            <div class="pt-2">
                <button type="submit" class="btn-primary w-full justify-center text-[16px] py-4"
                        :disabled="!accepted"
                        :class="!accepted ? 'opacity-50 cursor-not-allowed' : ''">
                    {{ __('messages.contact.submit') }}
                    <span class="material-symbols-outlined text-[20px]">send</span>
                </button>
            </div>
        </form>
    </div>

</section>

@endsection
