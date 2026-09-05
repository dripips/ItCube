@extends('layouts.app')
@section('title', 'ItCube — '.__('учебный центр'))

@section('content')
    <section class="py-10 md:py-16">
        <div class="max-w-2xl">
            <span class="badge-brand">{{ __('Учебный центр') }}</span>
            <h1 class="mt-4 text-4xl font-bold md:text-5xl">
                {{ __('Учимся писать код, а не читать про него') }}
            </h1>
            <p class="mt-4 text-lg muted">
                {{ __('Задачи проверяются сразу: программа запускается на нескольких наборах данных, и видно, какой из них не сошёлся.') }}
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('directions.index') }}" class="btn-primary">{{ __('Направления') }}</a>
                @guest
                    <a href="{{ route('login') }}" class="btn-ghost">{{ __('Войти') }}</a>
                @endguest
            </div>
        </div>
    </section>

    <section class="mt-6">
        <h2 class="mb-4 text-xl font-semibold">{{ __('Чему учим') }}</h2>

        @if ($directions->isEmpty())
            <x-empty-state :title="__('Направлений пока нет')"/>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($directions as $direction)
                    <a href="{{ route('directions.show', $direction) }}"
                       class="surface group flex flex-col p-5 transition hover:-translate-y-0.5 hover:shadow-lg">
                        <x-cube class="h-8 w-8"/>
                        <h3 class="mt-4 font-semibold group-hover:text-brand-600 dark:group-hover:text-brand-400">{{ $direction->name }}</h3>
                        @if ($direction->age_range)
                            <span class="mt-1 text-xs muted">{{ $direction->age_range }} {{ __('лет') }}</span>
                        @endif
                        <p class="mt-2 line-clamp-3 text-sm muted">{{ $direction->description }}</p>
                        <div class="mt-4 flex gap-4 text-xs muted">
                            <span>{{ trans_choice('{0}нет предметов|{1}:count предмет|[2,4]:count предмета|[5,*]:count предметов', $direction->subjects_count, ['count' => $direction->subjects_count]) }}</span>
                            <span>{{ trans_choice('{0}нет групп|{1}:count группа|[2,4]:count группы|[5,*]:count групп', $direction->groups_count, ['count' => $direction->groups_count]) }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    @if ($posts->isNotEmpty())
        <section class="mt-12">
            <div class="mb-4 flex items-baseline justify-between">
                <h2 class="text-xl font-semibold">{{ __('Новости') }}</h2>
                <a href="{{ route('posts.index') }}" class="text-sm muted hover:underline">{{ __('Все новости') }}</a>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($posts as $post)
                    <a href="{{ route('posts.show', $post) }}" class="surface p-5 transition hover:shadow-lg">
                        <time class="text-xs muted">{{ $post->published_at->translatedFormat('j F Y') }}</time>
                        <h3 class="mt-1 font-semibold">{{ $post->title }}</h3>
                        <p class="mt-2 line-clamp-2 text-sm muted">{{ $post->excerpt }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
