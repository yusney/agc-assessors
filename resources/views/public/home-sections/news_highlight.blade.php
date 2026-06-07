@if(!empty($news))
<section class="w-full max-w-[1280px] mx-auto px-6 md:px-8 py-16 md:py-20" id="actualitat">
    <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-6">
        <div class="max-w-2xl">
            <h2 class="font-headline text-[36px] md:text-[56px] text-[#0f172a] mb-4 tracking-tight font-semibold leading-none">
                {{ $section->localized('title') }}
            </h2>
            @if($section->localized('subtitle'))
                <p class="text-[20px] text-[#64748B] font-light">{{ $section->localized('subtitle') }}</p>
            @endif
        </div>

        @if($section->localized('cta_label') && $section->cta_url)
            <a href="{{ url($section->cta_url) }}" class="hidden md:inline-flex items-center text-[#1E293B] font-medium text-[15px] hover:text-[#00346f] transition-colors pb-1 border-b-2 border-transparent hover:border-[#00346f]">
                {{ $section->localized('cta_label') }}
                <span class="material-symbols-outlined ml-1 text-[20px]">&#xe5c8;</span>
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
        @foreach(array_slice($news, 0, (int) $section->setting('limit', 3)) as $article)
            <a href="{{ url('/actualitat/' . $article->slug()) }}" class="news-card group">
                <div class="aspect-[4/3] bg-[#e7e8ef] relative overflow-hidden">
                    @if($article->coverUrl())
                        <img src="{{ $article->coverUrl() }}"
                             alt="{{ $article->title()->get(app()->getLocale()) }}"
                             class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-[#e7e8ef] to-[#d9d9e1]"></div>
                    @endif
                    <div class="absolute top-5 left-5 bg-white/95 backdrop-blur-md text-[#0f172a] text-[13px] font-medium tracking-wide px-4 py-1.5 rounded-full shadow-sm">
                        {{ __('messages.nav.news') }}
                    </div>
                </div>
                <div class="p-8 flex flex-col flex-grow">
                    <h3 class="font-headline font-semibold text-[24px] leading-tight text-[#0f172a] mb-4 group-hover:text-[#00346f] transition-colors duration-300">
                        {{ $article->title()->get(app()->getLocale()) }}
                    </h3>
                    <p class="text-[16px] text-[#64748B] line-clamp-3 mb-8 flex-grow font-light leading-relaxed">
                        {{ $article->excerpt()->get(app()->getLocale()) }}
                    </p>
                    <div class="flex items-center justify-between mt-auto pt-6 border-t border-[#E2E8F0]">
                        <span class="text-[14px] text-[#64748B] font-medium">{{ $article->publishedAt()?->format('d M Y') }}</span>
                        <span class="material-symbols-outlined text-[#64748B] group-hover:text-[#00346f] transition-colors">&#xf8ce;</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    @if($section->localized('cta_label') && $section->cta_url)
        <div class="mt-10 text-center md:hidden">
            <a href="{{ url($section->cta_url) }}" class="btn-outline w-full justify-center">{{ $section->localized('cta_label') }}</a>
        </div>
    @endif
</section>
@endif
