@php $locale = app()->getLocale(); @endphp
<article class="flex flex-col group cursor-pointer">
    <a href="{{ url('/actualitat/' . $article->slug()) }}"
       class="relative w-full aspect-video overflow-hidden rounded-xl bg-[#e7e8ef] mb-6 block">
        @if($article->coverUrl())
            <img src="{{ $article->coverUrl() }}"
                 alt="{{ $article->title()->get($locale) }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                 loading="lazy">
        @else
            <div class="w-full h-full bg-gradient-to-br from-[#e7e8ef] to-[#d9d9e1]
                        group-hover:scale-105 transition-transform duration-700 ease-out"></div>
        @endif
    </a>
    <div class="flex-grow flex flex-col">
        <div class="flex items-center gap-3 mb-3">
            <span class="text-[#00B4D8] text-[13px] uppercase tracking-wider font-semibold font-body">
                {{ __('messages.nav.news') }}
            </span>
            <span class="text-[#E2E8F0]">•</span>
            <time class="text-[13px] text-[#5c5f61]">
                {{ $article->publishedAt()?->format('d M Y') }}
            </time>
        </div>
        <h3 class="font-headline text-[24px] text-[#0f172a] font-semibold leading-tight line-clamp-3 mb-3
                   group-hover:text-[#00346f] transition-colors duration-300">
            <a href="{{ url('/actualitat/' . $article->slug()) }}">
                {{ $article->title()->get($locale) }}
            </a>
        </h3>
        <p class="text-[16px] text-[#424751] line-clamp-3 mb-4 flex-grow leading-relaxed">
            {{ $article->excerpt()->get($locale) }}
        </p>
        <div class="mt-auto">
            <a href="{{ url('/actualitat/' . $article->slug()) }}"
               class="inline-flex items-center text-[#00346f] font-semibold text-[15px]
                      group-hover:text-[#00B4D8] transition-colors">
                {{ __('messages.news.read_more') }}
                <span class="material-symbols-outlined ml-1 text-[18px]
                             group-hover:translate-x-1 transition-transform">&#xe5c8;</span>
            </a>
        </div>
    </div>
</article>
