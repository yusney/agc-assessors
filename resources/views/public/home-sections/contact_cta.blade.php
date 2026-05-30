@php
    $isNewsletter = (bool) $section->setting('is_newsletter');
    $placeholder  = data_get($section->settings, 'newsletter_placeholder.' . app()->getLocale(), __('messages.home.newsletter_placeholder'));
    $legal        = data_get($section->settings, 'newsletter_legal.' . app()->getLocale(), __('messages.home.newsletter_legal'));
@endphp

<section class="w-full max-w-[1280px] mx-auto px-6 md:px-8 pt-8 pb-16 md:pb-20">
    <div class="bg-[#f3f3fa] rounded-[2.5rem] p-10 md:p-16 lg:p-20 flex flex-col lg:flex-row items-center justify-between gap-12 relative overflow-hidden border border-[#E2E8F0]/50">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-[#00346f]/5 rounded-full blur-[100px] -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        <div class="lg:w-5/12 relative z-10 text-center lg:text-left">
            <h2 class="font-headline text-[36px] md:text-[44px] font-semibold text-[#1E293B] mb-4 tracking-tight">
                {{ $section->localized('title') }}
            </h2>
            @if($section->localized('subtitle'))
                <p class="text-[18px] text-[#64748B] font-light leading-relaxed">{{ $section->localized('subtitle') }}</p>
            @endif
        </div>

        <div class="w-full lg:w-6/12 relative z-10">
            @if($isNewsletter)
                <form class="flex flex-col sm:flex-row gap-4 max-w-xl mx-auto lg:mx-0 lg:ml-auto">
                    <input type="email" required placeholder="{{ $placeholder }}" class="flex-grow bg-white border border-[#c2c6d3]/50 rounded-full px-6 py-4 text-[16px] text-[#1E293B] placeholder:text-[#64748B]/60 focus:outline-none focus:ring-1 focus:ring-[#00346f] focus:border-[#00346f] shadow-sm transition-all">
                    <button type="submit" class="btn-primary text-[16px] px-8 py-4 whitespace-nowrap">
                        {{ $section->localized('cta_label') }}
                    </button>
                </form>
                <p class="text-[13px] text-[#64748B]/70 mt-4 text-center lg:text-right font-light">{{ $legal }}</p>
            @elseif($section->localized('cta_label') && $section->cta_url)
                <div class="flex justify-center lg:justify-end">
                    <a href="{{ url($section->cta_url) }}" class="btn-primary text-[16px] px-10 py-4">
                        {{ $section->localized('cta_label') }}
                        <span class="material-symbols-outlined text-[20px]">arrow_right_alt</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>
