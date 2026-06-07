{{--
    Service Modal — Shared Alpine.js component
    Usage: include in any page that sets these Alpine data properties:
      modalOpen, modalName, modalDescription, modalIcon, modalCoverUrl, modalUrl
--}}

{{-- Backdrop --}}
<div
    x-show="modalOpen"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 bg-black/50"
    @click="modalOpen = false"
></div>

{{-- Modal card --}}
<div
    x-show="modalOpen"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    @keydown.escape.window="modalOpen = false"
>
    <div class="bg-white rounded-[1.5rem] shadow-2xl max-w-lg w-full overflow-hidden" @click.stop>
        {{-- Header with icon/image --}}
        <div class="relative bg-[#f3f3fa] px-8 pt-8 pb-6 text-center">
            {{-- Close button --}}
            <button
                @click="modalOpen = false"
                class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white shadow flex items-center justify-center text-[#64748B] hover:text-[#1E293B] transition-colors"
                aria-label="Close"
            >
                <span class="material-symbols-outlined text-[20px]">&#xe5cd;</span>
            </button>

            {{-- Cover image or icon --}}
            <template x-if="modalCoverUrl">
                <img :src="modalCoverUrl" :alt="modalName" class="w-20 h-20 rounded-2xl object-cover mx-auto mb-4 shadow-md">
            </template>
            <template x-if="!modalCoverUrl">
                <div class="w-20 h-20 rounded-2xl bg-[#00346f]/10 flex items-center justify-center mx-auto mb-4">
                    <span
                        class="material-symbols-outlined text-[#00346f] text-[36px]"
                        x-text="modalIcon"
                        style="font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 40"
                    ></span>
                </div>
            </template>
        </div>

        {{-- Body --}}
        <div class="px-8 py-6">
            <h3 class="font-headline font-semibold text-[24px] text-[#1E293B] mb-3 text-center" x-text="modalName"></h3>
            <p class="text-[15px] text-[#64748B] leading-relaxed font-light text-center mb-6" x-text="modalDescription"></p>
            <div class="text-center">
                <a
                    :href="modalUrl"
                    class="inline-flex items-center gap-2 px-8 py-3 bg-[#00346f] text-white rounded-full font-medium text-[15px] hover:bg-[#00244f] transition-colors"
                >
                    {{ __('messages.services.cta') ?? 'Més info' }}
                    <span class="material-symbols-outlined text-[18px]">&#xe5c8;</span>
                </a>
            </div>
        </div>
    </div>
</div>
