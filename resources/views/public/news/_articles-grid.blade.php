@php $locale = app()->getLocale(); @endphp
@foreach($news as $article)
    @include('public.news._article-card', ['article' => $article])
@endforeach
