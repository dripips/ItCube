@extends('layouts.app')
@section('title', $lesson->title.' — ItCube')

@section('content')
    <x-page-header :title="$lesson->title"
                   :subtitle="$lesson->summary"
                   :back="route('learn.index')">{{ __('Моё обучение') }}</x-page-header>

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if ($lesson->content)
                <div class="surface prose-lesson p-6">{!! nl2br(e($lesson->content)) !!}</div>
            @endif
        </div>

        <aside class="space-y-6">
            @if ($lesson->assignments->isNotEmpty())
                <div>
                    <h2 class="mb-3 text-sm font-semibold">{{ __('Задачи') }}</h2>
                    <div class="space-y-2">
                        @foreach ($lesson->assignments as $assignment)
                            @php($best = $bestByAssignment->get($assignment->id))
                            <a href="{{ route('learn.assignments.show', $assignment) }}" class="surface block p-4 transition hover:shadow-lg">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="font-medium">{{ $assignment->title }}</span>
                                    <x-difficulty :level="$assignment->difficulty"/>
                                </div>
                                <div class="mt-2 flex items-center gap-2 text-xs muted">
                                    <span class="font-mono">{{ $assignment->language }}</span>
                                    @if ($best)
                                        <span class="{{ $best->passed() ? 'badge-pass' : 'badge-fail' }}">{{ $best->passed_count }}/{{ $best->total_count }}</span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($lesson->quizzes->isNotEmpty())
                <div>
                    <h2 class="mb-3 text-sm font-semibold">{{ __('Тесты') }}</h2>
                    <div class="space-y-2">
                        @foreach ($lesson->quizzes as $quiz)
                            @continue (! $quiz->published)
                            <a href="{{ route('learn.quizzes.show', $quiz) }}" class="surface block p-4 transition hover:shadow-lg">
                                <span class="font-medium">{{ $quiz->title }}</span>
                                @if ($quiz->time_limit_minutes)
                                    <p class="mt-1 text-xs muted">{{ $quiz->time_limit_minutes }} {{ __('мин') }}</p>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>
@endsection
