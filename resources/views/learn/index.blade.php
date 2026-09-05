@extends('layouts.app')
@section('title', __('Моё обучение').' — ItCube')

@section('content')
    <x-page-header :title="__('Моё обучение')"
                   :subtitle="__('Занятия направлений, на которые вы записаны')"/>

    @if ($openAssessments->isNotEmpty())
        <div class="mb-8 rounded-[var(--radius-card)] border border-amber-500/30 bg-amber-500/8 p-5">
            <h2 class="font-semibold">{{ __('Идёт контрольная') }}</h2>
            <ul class="mt-3 space-y-2">
                @foreach ($openAssessments as $assessment)
                    <li class="flex flex-wrap items-center gap-3 text-sm">
                        <a href="{{ route('learn.assessments.show', $assessment) }}" class="font-medium hover:underline">{{ $assessment->title }}</a>
                        <span class="muted">{{ $assessment->group->name }}</span>
                        @if ($assessment->closes_at)
                            <span class="badge-neutral">{{ __('до') }} {{ $assessment->closes_at->translatedFormat('j F, H:i') }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($groups->isEmpty())
        <x-empty-state :title="__('Вы пока не записаны ни в одну группу')"
                       :hint="__('Как только преподаватель добавит вас в группу, здесь появятся занятия')"/>
    @else
        <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($groups as $group)
                <div class="surface p-5">
                    <span class="badge-brand">{{ $group->direction->name }}</span>
                    <h2 class="mt-3 font-semibold">{{ $group->name }}</h2>
                    @if ($group->teacher)
                        <p class="mt-1 text-sm muted">{{ $group->teacher->fullName() }}</p>
                    @endif
                    <ul class="mt-3 space-y-0.5 text-xs muted">
                        @foreach ($group->schedules as $slot)
                            <li>{{ App\Support\Week::name($slot->day_of_week) }}, {{ substr($slot->starts_at, 0, 5) }}–{{ substr($slot->ends_at, 0, 5) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <h2 class="mb-4 text-lg font-semibold">{{ __('Занятия') }}</h2>

        @forelse ($lessons->groupBy(fn ($lesson) => $lesson->subject->name) as $subjectName => $subjectLessons)
            <h3 class="mt-6 mb-3 text-sm font-semibold muted">{{ $subjectName }}</h3>

            <div class="space-y-3">
                @foreach ($subjectLessons as $lesson)
                    @php($done = $lesson->assignments->filter(fn ($a) => $solvedAssignmentIds->contains($a->id))->count())
                    <a href="{{ route('learn.lessons.show', $lesson) }}" class="surface flex items-center gap-4 p-4 transition hover:shadow-lg">
                        <div class="min-w-0 flex-1">
                            <h4 class="font-medium">{{ $lesson->title }}</h4>
                            @if ($lesson->summary)
                                <p class="mt-0.5 line-clamp-1 text-sm muted">{{ $lesson->summary }}</p>
                            @endif
                        </div>

                        @if ($lesson->assignments->isNotEmpty())
                            <span class="{{ $done === $lesson->assignments->count() ? 'badge-pass' : 'badge' }} shrink-0">
                                {{ __('задачи') }} {{ $done }}/{{ $lesson->assignments->count() }}
                            </span>
                        @endif

                        @if ($lesson->quizzes->isNotEmpty())
                            <span class="badge-neutral shrink-0">{{ trans_choice('{1}:count тест|[2,4]:count теста|[5,*]:count тестов', $lesson->quizzes->count(), ['count' => $lesson->quizzes->count()]) }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @empty
            <x-empty-state :title="__('Занятий пока нет')"/>
        @endforelse
    @endif
@endsection
