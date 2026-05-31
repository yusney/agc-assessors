@php
$locale = app()->getLocale();
$cookiePolicyUrl = route('pages.show', ['slug' => 'cookie-policy']);
@endphp

{{-- Cookie Consent Banner --}}
<div
    x-data="cookieBanner()"
    x-show="visible"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-y-full opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="translate-y-full opacity-0"
    class="fixed bottom-0 left-0 right-0 z-[100]"
    style="display: none;"
>
    {{-- Backdrop for customize modal --}}
    <div
        x-show="customizing"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 bg-black/30 z-[99]"
        style="display: none;"
        @click="customizing = false"
    ></div>

    {{-- Banner --}}
    <div class="bg-white border-t border-[#E2E8F0] shadow-[0_-4px_20px_rgba(0,0,0,0.08)]">
        <div class="max-w-[1280px] mx-auto px-6 md:px-8 py-5 md:py-6">
            
            {{-- Initial state --}}
            <div x-show="!customizing" class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex-1">
                    <p class="text-[15px] text-[#475569] leading-relaxed">
                        <span class="font-semibold text-[#1E293B]">{{ __('messages.cookies.banner_title') }}</span>
                        {{ __('messages.cookies.banner_text') }}
                        <a href="{{ $cookiePolicyUrl }}" class="text-[#00346f] underline hover:text-[#00346f]/80 transition-colors">
                            {{ __('messages.cookies.learn_more') }}
                        </a>
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <button
                        @click="rejectAll()"
                        class="px-5 py-2.5 rounded-[12px] border border-[#CBD5E1] text-[14px] font-medium text-[#475569] hover:border-[#94A3B8] hover:text-[#1E293B] transition-colors"
                    >
                        {{ __('messages.cookies.reject_all') }}
                    </button>
                    <button
                        @click="customizing = true"
                        class="px-5 py-2.5 rounded-[12px] border border-[#00346f] text-[14px] font-medium text-[#00346f] hover:bg-[#00346f]/5 transition-colors"
                    >
                        {{ __('messages.cookies.customize') }}
                    </button>
                    <button
                        @click="acceptAll()"
                        class="px-5 py-2.5 rounded-[12px] bg-[#00346f] text-[14px] font-medium text-white hover:bg-[#00346f]/90 transition-colors"
                    >
                        {{ __('messages.cookies.accept_all') }}
                    </button>
                </div>
            </div>

            {{-- Customize state --}}
            <div x-show="customizing" x-cloak class="flex flex-col gap-5">
                <div class="flex items-center justify-between">
                    <h3 class="font-headline text-[20px] font-semibold text-[#1E293B]">
                        {{ __('messages.cookies.customize') }}
                    </h3>
                    <button @click="customizing = false" class="text-[#64748B] hover:text-[#1E293B] transition-colors">
                        <span class="material-symbols-outlined text-[24px]">close</span>
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- Necessary --}}
                    <div class="flex items-start justify-between gap-4 p-4 rounded-[12px] bg-[#F1F5F9]">
                        <div class="flex-1">
                            <h4 class="font-medium text-[15px] text-[#1E293B] mb-1">{{ __('messages.cookies.necessary_title') }}</h4>
                            <p class="text-[13px] text-[#64748B] leading-relaxed">{{ __('messages.cookies.necessary_text') }}</p>
                        </div>
                        <div class="relative inline-flex h-6 w-11 items-center rounded-full bg-[#00346f] shrink-0 cursor-not-allowed opacity-70">
                            <span class="translate-x-5 inline-block h-4 w-4 rounded-full bg-white transition-transform"></span>
                        </div>
                    </div>

                    {{-- Analytics --}}
                    <div class="flex items-start justify-between gap-4 p-4 rounded-[12px] border border-[#E2E8F0]">
                        <div class="flex-1">
                            <h4 class="font-medium text-[15px] text-[#1E293B] mb-1">{{ __('messages.cookies.analytics_title') }}</h4>
                            <p class="text-[13px] text-[#64748B] leading-relaxed">{{ __('messages.cookies.analytics_text') }}</p>
                        </div>
                        <button
                            @click="preferences.analytics = !preferences.analytics"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors shrink-0"
                            :class="preferences.analytics ? 'bg-[#00346f]' : 'bg-[#CBD5E1]"
                        >
                            <span
                                class="inline-block h-4 w-4 rounded-full bg-white transition-transform"
                                :class="preferences.analytics ? 'translate-x-5' : 'translate-x-1'"
                            ></span>
                        </button>
                    </div>

                    {{-- Marketing --}}
                    <div class="flex items-start justify-between gap-4 p-4 rounded-[12px] border border-[#E2E8F0]">
                        <div class="flex-1">
                            <h4 class="font-medium text-[15px] text-[#1E293B] mb-1">{{ __('messages.cookies.marketing_title') }}</h4>
                            <p class="text-[13px] text-[#64748B] leading-relaxed">{{ __('messages.cookies.marketing_text') }}</p>
                        </div>
                        <button
                            @click="preferences.marketing = !preferences.marketing"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors shrink-0"
                            :class="preferences.marketing ? 'bg-[#00346f]' : 'bg-[#CBD5E1]"
                        >
                            <span
                                class="inline-block h-4 w-4 rounded-full bg-white transition-transform"
                                :class="preferences.marketing ? 'translate-x-5' : 'translate-x-1'"
                            ></span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <a href="{{ $cookiePolicyUrl }}" class="text-[14px] text-[#00346f] underline hover:text-[#00346f]/80 transition-colors">
                        {{ __('messages.cookies.learn_more') }}
                    </a>
                    <button
                        @click="savePreferences()"
                        class="px-6 py-2.5 rounded-[12px] bg-[#00346f] text-[14px] font-medium text-white hover:bg-[#00346f]/90 transition-colors"
                    >
                        {{ __('messages.cookies.save_preferences') }}
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function cookieBanner() {
    const STORAGE_KEY = 'cookieConsent';
    const stored = localStorage.getItem(STORAGE_KEY);
    const hasConsent = stored !== null;
    let parsed = { necessary: true, analytics: false, marketing: false };
    
    if (hasConsent) {
        try {
            parsed = JSON.parse(stored);
        } catch (e) {
            parsed = { necessary: true, analytics: false, marketing: false };
        }
    }

    // Expose consent globally for other scripts
    window.cookieConsent = parsed;

    return {
        visible: !hasConsent,
        customizing: false,
        preferences: {
            necessary: true,
            analytics: parsed.analytics || false,
            marketing: parsed.marketing || false,
        },
        acceptAll() {
            this.preferences = { necessary: true, analytics: true, marketing: true };
            this.saveConsent();
            this.visible = false;
        },
        rejectAll() {
            this.preferences = { necessary: true, analytics: false, marketing: false };
            this.saveConsent();
            this.visible = false;
        },
        savePreferences() {
            this.saveConsent();
            this.visible = false;
        },
        saveConsent() {
            const consent = {
                ...this.preferences,
                timestamp: new Date().toISOString(),
            };
            localStorage.setItem(STORAGE_KEY, JSON.stringify(consent));
            window.cookieConsent = consent;
            // Dispatch event so other scripts can react
            window.dispatchEvent(new CustomEvent('cookieConsentUpdated', { detail: consent }));
        }
    };
}
</script>
