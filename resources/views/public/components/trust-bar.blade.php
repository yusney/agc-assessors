{{-- Trust Bar — Certifications & Accreditations (static preview) --}}
<section class="w-full bg-white border-y border-[#E2E8F0]">
    <div class="max-w-[1280px] mx-auto px-6 md:px-8 py-8 md:py-10">

        <div class="flex flex-col md:flex-row items-center justify-center gap-10 md:gap-20">

            {{-- Certification 1: UNE 420001 --}}
            <div class="flex items-center gap-4 group">
                <div class="w-14 h-14 rounded-2xl bg-[#00346f]/8 flex items-center justify-center
                            group-hover:bg-[#00346f]/12 transition-colors duration-300 flex-shrink-0">
                    <span class="material-symbols-outlined text-[#00346f] text-[28px]">verified</span>
                </div>
                <div>
                    <p class="text-[14px] font-semibold text-[#0f172a] leading-tight">{{ __('messages.trust.une_title') }}</p>
                    <p class="text-[13px] text-[#64748B]">{{ __('messages.trust.une_subtitle') }}</p>
                </div>
            </div>

            {{-- Divider --}}
            <span class="hidden md:block w-px h-12 bg-[#E2E8F0]"></span>

            {{-- Badge: 25+ years --}}
            <div class="flex items-center gap-4 group">
                <div class="w-14 h-14 rounded-2xl bg-[#00346f]/8 flex items-center justify-center
                            group-hover:bg-[#00346f]/12 transition-colors duration-300 flex-shrink-0">
                    <span class="material-symbols-outlined text-[#00346f] text-[28px]">history</span>
                </div>
                <div>
                    <p class="text-[14px] font-semibold text-[#0f172a] leading-tight">{{ __('messages.trust.experience_title') }}</p>
                    <p class="text-[13px] text-[#64748B]">{{ __('messages.trust.experience_subtitle') }}</p>
                </div>
            </div>

        </div>

    </div>
</section>
