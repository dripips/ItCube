@extends('layouts.app')
@section('title', __('Новости').' — ItCube')

@section('content')
    <x-page-header :title="__('Новости')"/>

    @forelse ($posts as $post)
        <a href="{{ route('posts.show', $post) }}" class="surface mb-3 block p-5 transition hover:shadow-lg">
            <time class="text-xs muted">{{ $post->published_at->translatedFormat('j F Y') }}</time>
            <h2 class="mt-1 font-semibold">{{ $post->title }}</h2>
            <p class="mt-2 text-sm muted">{{ $post->excerpt }}</p>
        </a>
    @empty
        <x-empty-state :title="__('Новостей пока нет')"/>
    @endforelse

    <div class="mt-6">{{ $posts->links() }}</div>
@endsection
