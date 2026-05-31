@extends('layouts.public')

@php
    /** Helper: Spatie Translatable may return a Translation object or a plain string/null */
    $locale = app()->getLocale();

    $fieldValue = fn (mixed $field): string => match (true) {
        $field instanceof \Spatie\Translatable\Attributes\Translation => $field->get($locale) ?? '',
        is_string($field) => $field,
        default => '',
    };

    $seoTitle       = $fieldValue($page->seo_title);
    $seoDescription = $fieldValue($page->seo_description);
    $pageTitle      = $fieldValue($page->title);
    $pageContent    = $fieldValue($page->content);
@endphp

@section('seo_title', $seoTitle ?: $pageTitle)
@section('seo_description', $seoDescription)
@if($page->seo_canonical)
    @section('seo_canonical', $page->seo_canonical)
@endif

@section('content')

<main class="pt-32 pb-32">
    <article class="max-w-[720px] mx-auto px-6 md:px-0">

        <h1 class="font-headline text-[36px] md:text-[48px] leading-[1.15] tracking-tight text-[#1E293B] mb-8">
            {{ $pageTitle }}
        </h1>

        <div class="prose prose-lg max-w-none
                    prose-headings:font-headline prose-headings:text-[#1E293B]
                    prose-p:text-[#424751] prose-p:leading-[1.8] prose-p:text-[17px]
                    prose-a:text-[#00346f] prose-a:no-underline hover:prose-a:text-[#00B4D8]
                    prose-blockquote:border-l-[3px] prose-blockquote:border-[#00346f]
                    prose-blockquote:text-[#00346f] prose-blockquote:not-italic prose-blockquote:pl-8
                    prose-li:text-[#424751]">
            {!! $pageContent !!}
        </div>

    </article>
</main>

@endsection
