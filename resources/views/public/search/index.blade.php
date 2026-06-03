@extends('layouts.public')

@section('seo_title', __('messages.search.title') . ' – AGC Assessors')
@section('seo_description', __('messages.search.title'))

@push('head')
<style>
mark {
    background: linear-gradient(120deg, #00B4D8 0%, #00346f 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 600;
    padding: 0;
}
</style>
@endpush

@section('content')

{{-- Hero / search header --}}
<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto py-16 md:py-20">
    <h1 class="font-headline text-[48px] font-semibold text-[#00346f] mb-6 tracking-tight leading-none">
        {{ __('messages.search.title') }}
    </h1>

    <form action="{{ route('search') }}" method="GET" class="flex gap-3 max-w-2xl">
        <input
            type="text"
            name="q"
            value="{{ $query }}"
            placeholder="{{ __('messages.search.placeholder') }}"
            autofocus
            class="flex-1 px-5 py-3 rounded-xl border border-[#E2E8F0] bg-white text-[#1E293B]
                   text-[16px] placeholder-[#94A3B8] focus:outline-none focus:border-[#00346f]
                   focus:ring-2 focus:ring-[#00346f]/10 transition-all"
        >
        <button type="submit"
                class="btn-primary px-6 py-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">search</span>
            <span>{{ __('messages.search.title') }}</span>
        </button>
    </form>
</section>

{{-- Results --}}
<section class="w-full px-6 md:px-8 max-w-[1280px] mx-auto pb-24">

    @if(mb_strlen($query) > 0 && mb_strlen($query) < 3)
        <p class="text-[#64748B] text-[16px] py-8">
            {{ __('messages.search.min_chars') }}
        </p>

    @elseif(mb_strlen($query) >= 3)

        {{-- Count --}}
        <p class="text-[#64748B] text-[15px] mb-8">
            {{ __('messages.search.results_for') }}
            <span class="font-semibold text-[#1E293B]">"{{ $query }}"</span>
            @if($paginator)
                &mdash;
                {{ trans_choice('messages.search.results_count', $paginator->total()) }}
            @endif
        </p>

        @if($results->isEmpty())
            {{-- No results --}}
            <div class="text-center py-20">
                <span class="material-symbols-outlined text-[64px] text-[#CBD5E1]">search_off</span>
                <p class="mt-4 text-[20px] font-semibold text-[#1E293B]">
                    {{ __('messages.search.no_results') }}
                </p>
                <p class="mt-2 text-[16px] text-[#64748B]">
                    {{ __('messages.search.no_results_subtitle') }}
                    <a href="{{ route('contact') }}" class="text-[#00346f] hover:text-[#00B4D8] transition-colors underline">
                        {{ __('messages.search.contact_link') }}
                    </a>.
                </p>
            </div>
        @else
            {{-- Result cards --}}
            <div class="flex flex-col gap-6">
                @foreach($results as $result)
                    @php
                        $url = match($result->source_type) {
                            'news'    => route('news.show', $result->slug),
                            'service' => route('services.show', $result->slug),
                            default   => route('pages.show', $result->slug),
                        };
                        $badgeClass = match($result->source_type) {
                            'news'    => 'bg-blue-50 text-blue-700 border border-blue-200',
                            'service' => 'bg-green-50 text-green-700 border border-green-200',
                            default   => 'bg-slate-100 text-slate-600 border border-slate-200',
                        };
                        $badgeLabel = match($result->source_type) {
                            'news'    => __('messages.search.source_news'),
                            'service' => __('messages.search.source_service'),
                            default   => __('messages.search.source_page'),
                        };
                    @endphp
                    <article class="bg-white rounded-2xl border border-[#E2E8F0] p-6 hover:border-[#00346f]/30
                                    hover:shadow-md transition-all duration-200 group">
                        <div class="flex items-start gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="inline-block text-[12px] font-medium px-2.5 py-0.5 rounded-full {{ $badgeClass }}">
                                        {{ $badgeLabel }}
                                    </span>
                                </div>
                                <h2 class="text-[18px] font-semibold text-[#1E293B] mb-2 group-hover:text-[#00346f] transition-colors">
                                    <a href="{{ $url }}" class="hover:underline">
                                        {{ $result->title }}
                                    </a>
                                </h2>
                                @if($result->snippet)
                                    <p class="text-[15px] text-[#424751] leading-relaxed line-clamp-3">
                                        {!! $result->snippet !!}
                                    </p>
                                @endif
                                <a href="{{ $url }}"
                                   class="inline-flex items-center gap-1 mt-3 text-[13px] text-[#00346f]
                                          hover:text-[#00B4D8] transition-colors font-medium">
                                    <span>{{ $url }}</span>
                                    <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($paginator && $paginator->hasPages())
                <div class="mt-12 flex justify-center">
                    {{ $paginator->links() }}
                </div>
            @endif
        @endif

    @endif

</section>

@endsection
