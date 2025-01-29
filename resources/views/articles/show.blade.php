@extends('layouts.master')
@section('title')
    @lang('translation.dashboards')
@endsection

@section('css')

@endsection

@section('content')
    <h1>{{ $article->title }}</h1>
    <p>{{ $article->content }}</p>
    <a href="{{ route('articles.index') }}" class="btn btn-secondary">Back to Articles</a>
@endsection

@section('script')
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
