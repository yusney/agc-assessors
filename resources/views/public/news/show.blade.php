@extends('layouts.public')

@section('seo_title', $article->seo()->title()->get(app()->getLocale()) ?: $article->title()->get(app()->getLocale()))
@section('seo_description', $article->seo()->description()->get(app()->getLocale()))
@if($article->seo()->canonicalUrl())
    @section('seo_canonical', $article->seo()->canonicalUrl())
@endif

@push('head')
<style>
    @keyframes readProgress { from { width: 0% } to { width: 100% } }
    #reading-bar { animation: readProgress 10s ease-out forwards; }
</style>
@endpush

@section('content')

{{-- Reading progress bar --}}
<div id="reading-bar"
     class="fixed top-0 left-0 h-[3px] bg-[#00B4D8] z-[60] w-0"
     x-data
     x-init="
        let bar = $el;
        window.addEventListener('scroll', () => {
            let el = document.documentElement;
            let pct = el.scrollTop / (el.scrollHeight - el.clientHeight);
            bar.style.width = Math.min(pct * 100, 100) + '%';
            bar.style.animation = 'none';
        });
     ">
</div>

{{-- Hero image 70vh --}}
<div class="relative w-full h-[70vh] min-h-[500px] bg-[#e7e8ef] mt-0">
    <div class="absolute inset-0 bg-black/40 z-10"></div>
    @if($article->coverUrl())
        <img src="{{ $article->coverUrl() }}"
             alt="{{ $article->title()->get(app()->getLocale()) }}"
             class="absolute inset-0 w-full h-full object-cover">
    @else
        <div class="absolute inset-0 bg-gradient-to-br from-[#e7e8ef] to-[#c2c6d3]"></div>
    @endif
    <div class="absolute bottom-0 left-0 w-full z-20
                bg-gradient-to-t from-[#f9f9ff] to-transparent pt-32 pb-12">
        <div class="max-w-[720px] mx-auto px-6 md:px-0 text-center">
            <div class="flex items-center justify-center gap-2 mb-6">
                <span class="inline-flex items-center px-3 py-1 rounded-full
                             border border-[#00346f]/30 bg-[#f9f9ff]/80 backdrop-blur-sm
                             text-[#00346f] text-[13px] font-semibold uppercase tracking-wider">
                    {{ __('messages.nav.news') }}
                </span>
            </div>
            <h1 class="font-headline text-[36px] md:text-[56px] lg:text-[64px] leading-[1.05] tracking-[-0.02em] text-[#0f172a] mb-4">
                {{ $article->title()->get(app()->getLocale()) }}
            </h1>
            <div class="flex items-center justify-center gap-2 text-[#5c5f61] text-[13px] uppercase tracking-widest">
                @if($article->publishedAt())
                    <time>{{ $article->publishedAt()->format('d \d\e F, Y') }}</time>
                    <span class="w-1 h-1 rounded-full bg-[#E2E8F0]"></span>
                @endif
                <span>{{ __('messages.news.read_time') }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Article body — two-column: sticky share sidebar + article --}}
<main class="pb-32">
    <div class="max-w-[900px] mx-auto px-6 xl:px-0 pt-12 flex gap-10 items-start">

        {{-- Sticky social sidebar (desktop only) --}}
        <aside
            class="hidden xl:flex flex-col gap-3 w-10 flex-shrink-0"
            x-data="{
                copied: false,
                shareLinkedIn() {
                    window.open('https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(window.location.href), '_blank', 'width=600,height=500');
                },
                shareWhatsApp() {
                    window.open('https://wa.me/?text=' + encodeURIComponent(document.title + ' ' + window.location.href), '_blank');
                },
                async copyLink() {
                    await navigator.clipboard.writeText(window.location.href);
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                }
            }"
        >
            <div class="sticky top-32 flex flex-col gap-3">
                {{-- LinkedIn --}}
                <button @click="shareLinkedIn()"
                        title="LinkedIn"
                        class="w-10 h-10 rounded-full bg-white border border-[#E2E8F0]
                               flex items-center justify-center text-[#5c5f61]
                               hover:text-[#0A66C2] hover:border-[#0A66C2] hover:-translate-y-0.5
                               shadow-sm transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                </button>

                {{-- WhatsApp --}}
                <button @click="shareWhatsApp()"
                        title="WhatsApp"
                        class="w-10 h-10 rounded-full bg-white border border-[#E2E8F0]
                               flex items-center justify-center text-[#5c5f61]
                               hover:text-[#25D366] hover:border-[#25D366] hover:-translate-y-0.5
                               shadow-sm transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
                    </svg>
                </button>

                {{-- Copy link --}}
                <button @click="copyLink()"
                        :title="copied ? '{{ __('messages.news.link_copied') }}' : '{{ __('messages.news.copy_link') }}'"
                        class="w-10 h-10 rounded-full border
                               flex items-center justify-center
                               shadow-sm transition-all duration-200 hover:-translate-y-0.5"
                        :class="copied
                            ? 'bg-[#00346f] border-[#00346f] text-white'
                            : 'bg-white border-[#E2E8F0] text-[#5c5f61] hover:text-[#00346f] hover:border-[#00346f]'">
                    <span class="material-symbols-outlined text-[18px]"
                          x-text="copied ? 'check' : 'link'"></span>
                </button>
            </div>
        </aside>

        <article class="min-w-0 flex-1">

        {{-- Content --}}
        <div class="prose prose-lg max-w-none
                    prose-headings:font-headline prose-headings:text-[#0f172a] prose-headings:tracking-tight
                    prose-h2:text-[32px] prose-h2:leading-[1.3] prose-h2:mt-12 prose-h2:mb-6
                    prose-p:text-[#424751] prose-p:leading-[1.8] prose-p:text-[17px]
                    prose-a:text-[#00346f] prose-a:no-underline hover:prose-a:text-[#00B4D8]
                    prose-blockquote:border-l-[3px] prose-blockquote:border-[#00346f]
                    prose-blockquote:pl-8 prose-blockquote:py-4
                    prose-blockquote:text-[#00346f] prose-blockquote:not-italic
                    prose-blockquote:font-serif prose-blockquote:text-2xl prose-blockquote:italic
                    prose-li:text-[#424751] prose-li:text-[17px]
                    [&>p:first-child]:text-xl [&>p:first-child]:font-medium [&>p:first-child]:text-[#0f172a] [&>p:first-child]:leading-relaxed">
            {!! $article->body()->get(app()->getLocale()) !!}
        </div>

        {{-- Mobile social --}}
        <div class="xl:hidden flex items-center justify-center gap-6 mt-12 pt-8 border-t border-[#E2E8F0]"
             x-data="{
                 copied: false,
                 shareLinkedIn() { window.open('https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(window.location.href), '_blank', 'width=600,height=500'); },
                 shareWhatsApp() { window.open('https://wa.me/?text=' + encodeURIComponent(document.title + ' ' + window.location.href), '_blank'); },
                 async copyLink() { await navigator.clipboard.writeText(window.location.href); this.copied = true; setTimeout(() => this.copied = false, 2000); }
             }">
            <span class="text-[13px] text-[#5c5f61] uppercase tracking-wider">
                {{ __('messages.news.share') }}
            </span>
            <div class="flex gap-3">
                <button @click="shareLinkedIn()" title="LinkedIn"
                        class="w-10 h-10 rounded-full bg-white border border-[#E2E8F0]
                               flex items-center justify-center text-[#5c5f61]
                               hover:text-[#0A66C2] hover:border-[#0A66C2] shadow-sm transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                </button>
                <button @click="shareWhatsApp()" title="WhatsApp"
                        class="w-10 h-10 rounded-full bg-white border border-[#E2E8F0]
                               flex items-center justify-center text-[#5c5f61]
                               hover:text-[#25D366] hover:border-[#25D366] shadow-sm transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
                    </svg>
                </button>
                <button @click="copyLink()"
                        :title="copied ? '{{ __('messages.news.link_copied') }}' : '{{ __('messages.news.copy_link') }}'"
                        class="w-10 h-10 rounded-full border flex items-center justify-center shadow-sm transition-colors"
                        :class="copied ? 'bg-[#00346f] border-[#00346f] text-white' : 'bg-white border-[#E2E8F0] text-[#5c5f61] hover:text-[#00346f] hover:border-[#00346f]'">
                    <span class="material-symbols-outlined text-[18px]" x-text="copied ? 'check' : 'link'"></span>
                </button>
            </div>
        </div>

        {{-- Newsletter box --}}
        <div class="mt-16 bg-[#f3f3fa] border border-[#E2E8F0]/50 rounded-3xl p-8 md:p-12 text-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-[#00346f]/5 rounded-full blur-3xl -mr-10 -mt-10"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-[#00B4D8]/5 rounded-full blur-3xl -ml-10 -mb-10"></div>
            <div class="relative z-10">
                <h3 class="font-headline text-[24px] font-bold text-[#1E293B] mb-2">
                    {{ __('messages.home.newsletter_title') }}
                </h3>
                <p class="text-[16px] text-[#64748B] mb-8 max-w-md mx-auto font-light leading-relaxed">
                    {{ __('messages.home.newsletter_subtitle') }}
                </p>

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
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-500"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-4"
                         class="mb-6 p-5 rounded-2xl bg-green-50 border border-green-200 flex items-start gap-4 shadow-sm text-left">
                        <span class="material-symbols-outlined text-green-600 text-[28px] flex-shrink-0">check_circle</span>
                        <div>
                            <p class="text-green-900 font-semibold text-[16px] leading-tight mb-1">{{ __('messages.home.newsletter_success') }}</p>
                            <p class="text-green-700 text-[14px]">{{ __('messages.home.newsletter_legal') }}</p>
                        </div>
                    </div>

                    <form action="{{ route('newsletter.store') }}" method="POST" class="flex flex-col gap-4 max-w-md mx-auto" @submit="submitForm">
                        @csrf
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="email" name="email" required
                                   placeholder="{{ __('messages.home.newsletter_placeholder') }}"
                                   class="flex-1 rounded-full border-[#c2c6d3] focus:border-[#00346f]
                                          focus:ring-1 focus:ring-[#00346f] text-[16px] py-3 px-6
                                          shadow-sm bg-white text-[#1E293B]">
                            <button type="submit" class="btn-primary px-8 py-3 text-[15px]"
                                    :disabled="!accepted || isSubmitting"
                                    :class="(!accepted || isSubmitting) ? 'opacity-50 cursor-not-allowed' : ''">
                                <span x-show="!isSubmitting">{{ __('messages.home.newsletter_cta') }}</span>
                                <span x-show="isSubmitting" class="material-symbols-outlined animate-spin">progress_activity</span>
                            </button>
                        </div>
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="newsletter_privacy" id="newsletter_privacy_news" value="1"
                                   x-model="accepted"
                                   class="mt-0.5 w-4 h-4 rounded border-[#c2c6d3] accent-[#00346f] cursor-pointer">
                            <label for="newsletter_privacy_news" class="text-[13px] text-[#64748B] cursor-pointer leading-relaxed">
                                {!! __('messages.home.newsletter_privacy', ['url' => LaravelLocalization::getLocalizedURL(app()->getLocale(), '/pages/privacy-policy')]) !!}
                            </label>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </article>
    </div>
</main>

@endsection
