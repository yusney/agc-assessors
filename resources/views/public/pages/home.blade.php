@extends('layouts.public')

@section('seo_title', __('messages.home.seo_title'))
@section('seo_description', __('messages.home.seo_description'))

@section('content')
@foreach($sections as $section)
    @includeIf('public.home-sections.' . $section->type, [
        'section' => $section,
        'services' => $services,
        'news' => $news,
    ])
@endforeach

@endsection
