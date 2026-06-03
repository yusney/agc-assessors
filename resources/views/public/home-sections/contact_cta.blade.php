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
                <div x-data="{
                    accepted: false,
                    showToast: false,
                    isSubmitting: false,
                    async submitForm($event) {
                        $event.preventDefault();
                        if (!this.accepted) return;
                        
                        this.isSubmitting = true;
                        const form = $event.target;
                        const formData = new FormData(form);
                        
                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                body: formData,
                                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            });
                            
                            if (response.ok || response.status === 302) {
                                this.showToast = true;
                                form.reset();
                                this.accepted = false;
                                setTimeout(() => this.showToast = false, 6000);
                            }
                        } catch (e) {
                            console.error('Newsletter submission failed:', e);
                        } finally {
                            this.isSubmitting = false;
                        }
                    }
                }">
                    <div x-show="showToast"
                         x-init="setTimeout(() => showToast = false, 6000)"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-500"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-4"
                         class="mb-4 p-5 rounded-2xl bg-green-50 border border-green-200 flex items-start gap-4 shadow-sm">
                        <span class="material-symbols-outlined text-green-600 text-[28px] flex-shrink-0">check_circle</span>
                        <div>
                            <p class="text-green-900 font-semibold text-[16px] leading-tight mb-1">{{ __('messages.home.newsletter_success') }}</p>
                            <p class="text-green-700 text-[14px]">{{ __('messages.home.newsletter_legal') }}</p>
                        </div>
                    </div>

                    <form action="{{ route('newsletter.store') }}" method="POST" class="flex flex-col gap-4 max-w-xl mx-auto lg:mx-0 lg:ml-auto" @submit="submitForm">
                        @csrf
                        <x-spam-protection />
                        <div class="flex flex-col sm:flex-row gap-4">
                            <input type="email" name="email" required placeholder="{{ $placeholder }}" class="flex-grow bg-white border border-[#c2c6d3]/50 rounded-full px-6 py-4 text-[16px] text-[#1E293B] placeholder:text-[#64748B]/60 focus:outline-none focus:ring-1 focus:ring-[#00346f] focus:border-[#00346f] shadow-sm transition-all">
                            <button type="submit" class="btn-primary text-[16px] px-8 py-4 whitespace-nowrap"
                                    :disabled="!accepted || isSubmitting"
                                    :class="(!accepted || isSubmitting) ? 'opacity-50 cursor-not-allowed' : ''">
                                <span x-show="!isSubmitting">{{ $section->localized('cta_label') }}</span>
                                <span x-show="isSubmitting" class="material-symbols-outlined animate-spin">progress_activity</span>
                            </button>
                        </div>
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="newsletter_privacy" id="newsletter_privacy_home" value="1"
                                   x-model="accepted"
                                   class="mt-0.5 w-4 h-4 rounded border-[#c2c6d3] accent-[#00346f] cursor-pointer">
                            <label for="newsletter_privacy_home" class="text-[13px] text-[#64748B] cursor-pointer leading-relaxed">
                                {!! __('messages.home.newsletter_privacy', ['url' => LaravelLocalization::getLocalizedURL(app()->getLocale(), '/pages/privacy-policy')]) !!}
                            </label>
                        </div>
                    </form>
                </div>
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
