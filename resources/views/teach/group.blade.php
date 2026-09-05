@extends('layouts.app')
@section('title', $group->name.' — ItCube')

@section('content')
    <x-page-header :title="$group->name"
                   :subtitle="$group->direction->name"
                   :back="route('teach.groups.index')">{{ __('Группы') }}</x-page-header>

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold">{{ __('Ученики') }}</h2>
                <a href="{{ route('teach.journal.index', ['group' => $group->slug]) }}" class="btn-ghost">{{ __('Отметить посещаемость') }}</a>
            </div>

            @if ($group->students->isEmpty())
                <x-empty-state :title="__('В группе пока никого нет')"/>
            @else
                <div class="surface divide-y" style="--tw-divide-opacity: 1">
                    @foreach ($group->students->sortBy('last_name') as $student)
                        <div class="flex items-center gap-3 px-5 py-3" @if (! $loop->first) style="border-top: 1px solid var(--line)" @endif>
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-500/12 text-xs font-medium text-brand-700 dark:text-brand-300">
                                {{ mb_substr($student->last_name ?? $student->username, 0, 1) }}
                            </span>
                            <span class="text-sm">{{ $student->fullName() }}</span>
                            <span class="ml-auto font-mono text-xs muted">{{ $student->username }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($assessments->isNotEmpty())
                <h2 class="mt-8 mb-4 text-lg font-semibold">{{ __('Контрольные') }}</h2>
                <div class="space-y-2">
                    @foreach ($assessments as $assessment)
                        <a href="{{ route('teach.assessments.show', $assessment) }}" class="surface flex items-center gap-4 p-4 transition hover:shadow-lg">
                            <span class="flex-1 font-medium">{{ $assessment->title }}</span>
                            <span class="{{ $assessment->isOpen() ? 'badge-brand' : 'badge' }}">{{ $assessment->isOpen() ? __('идёт') : __('закрыта') }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <aside class="space-y-4">
            <div class="surface p-5">
                <h2 class="mb-3 text-sm font-semibold">{{ __('Расписание') }}</h2>
                <ul class="space-y-1.5 text-sm">
                    @forelse ($group->schedules as $slot)
                        <li class="flex justify-between">
                            <span>{{ App\Support\Week::name($slot->day_of_week) }}</span>
                            <span class="font-mono tabular-nums muted">{{ substr($slot->starts_at, 0, 5) }}–{{ substr($slot->ends_at, 0, 5) }}</span>
                        </li>
                    @empty
                        <li class="muted">{{ __('не задано') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="surface p-5">
                <h2 class="mb-3 text-sm font-semibold">{{ __('Предметы направления') }}</h2>
                <ul class="space-y-1.5 text-sm">
                    @foreach ($group->direction->subjects as $subject)
                        <li class="flex justify-between gap-3">
                            <span>{{ $subject->name }}</span>
                            <span class="muted">{{ $subject->lessons->count() }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
    </div>
@endsection
