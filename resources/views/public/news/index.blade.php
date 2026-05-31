@extends('layouts.public')

@section('seo_title', __('messages.news.seo_title'))
@section('seo_description', __('messages.news.seo_description'))

@section('content')

{{-- Header --}}
<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto py-16 md:py-20">
    <h1 class="font-headline text-[48px] font-semibold text-[#00346f] mb-3 tracking-tight leading-none">
        {{ __('messages.news.title') }}
    </h1>
    <p class="text-[18px] text-[#424751] max-w-2xl leading-relaxed font-light">
        {{ __('messages.news.subtitle') }}
    </p>
</section>

{{-- Grid --}}
<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto pt-12 pb-24"
         x-data="infiniteScroll()"
         x-init="init()">
    @if(empty($news))
        <p class="text-[#64748B] text-center py-16">{{ __('messages.news.empty') }}</p>
    @else
    <div id="news-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
        @foreach($news as $article)
            @include('public.news._article-card', ['article' => $article])
        @endforeach
    </div>

    {{-- Loading spinner --}}
    <div id="loading-sentinel" class="mt-16 flex justify-center items-center gap-3"
         x-show="loading"
         x-cloak>
        <span class="inline-block w-6 h-6 border-2 border-[#00346f] border-t-transparent rounded-full animate-spin"></span>
        <span class="text-[15px] text-[#64748B]">{{ __('messages.news.loading') }}</span>
    </div>

    {{-- No more results --}}
    <div id="no-more" class="mt-16 text-center"
         x-show="!hasMore && !loading"
         x-cloak>
        <span class="text-[15px] text-[#64748B]">{{ __('messages.news.no_more') }}</span>
    </div>
    @endif
</section>

<script>
function infiniteScroll() {
    return {
        page: {{ $page }},
        hasMore: {{ $has_more ? 'true' : 'false' }},
        loading: false,
        sentinel: null,

        init() {
            if (!this.hasMore) return;

            this.sentinel = document.getElementById('loading-sentinel');
            if (!this.sentinel) return;

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && this.hasMore && !this.loading) {
                        this.loadMore();
                    }
                });
            }, {
                rootMargin: '200px',
            });

            observer.observe(this.sentinel);
        },

        async loadMore() {
            this.loading = true;
            this.page++;

            try {
                const response = await fetch(`{{ url('/actualitat') }}?page=${this.page}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) throw new Error('Network response was not ok');

                const data = await response.json();

                if (data.html) {
                    const grid = document.getElementById('news-grid');
                    const temp = document.createElement('div');
                    temp.innerHTML = data.html;
                    while (temp.firstChild) {
                        grid.appendChild(temp.firstChild);
                    }
                }

                this.hasMore = data.has_more ?? false;
            } catch (error) {
                console.error('Error loading more articles:', error);
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>

@endsection
