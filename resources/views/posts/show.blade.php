@extends('layouts.app')
@section('title', $post->title.' — ItCube')

@section('content')
    <article class="mx-auto max-w-2xl">
        <x-page-header :title="$post->title" :back="route('posts.index')">{{ __('Все новости') }}</x-page-header>

        <p class="-mt-4 mb-6 text-sm muted">
            {{ $post->published_at->translatedFormat('j F Y') }}@if ($post->author) · {{ $post->author->fullName() }}@endif
        </p>

        <div class="prose-lesson">{!! nl2br(e($post->content)) !!}</div>
    </article>
@endsection
