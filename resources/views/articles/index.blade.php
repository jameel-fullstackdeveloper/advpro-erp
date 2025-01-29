@extends('layouts.master')
@section('title')
    @lang('translation.dashboards')
@endsection

@section('css')

@endsection

@section('content')
    <h1>Articles</h1>
    <a href="{{ route('articles.create') }}" class="btn btn-primary">Create New Article</a>
    <ul>
        @foreach($articles as $article)
            <li>
                <a href="{{ route('articles.show', $article->id) }}">{{ $article->title }}</a>
                <a href="{{ route('articles.edit', $article->id) }}" class="btn btn-secondary">Edit</a>
                <form action="{{ route('articles.destroy', $article->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </li>
        @endforeach
    </ul>
    @endsection

@section('script')
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection

