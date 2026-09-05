@extends('layouts.app')
@section('title', $direction->name.' — ItCube')

@section('content')
    <x-page-header :title="$direction->name"
                   :subtitle="$direction->description"
                   :back="route('directions.index')">{{ __('Все направления') }}</x-page-header>

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <h2 class="mb-4 text-lg font-semibold">{{ __('Предметы') }}</h2>

            @forelse ($direction->subjects as $subject)
                <div class="surface mb-3 p-5">
                    <h3 class="font-medium">{{ $subject->name }}</h3>
                    @if ($subject->description)
                        <p class="mt-1 text-sm muted">{{ $subject->description }}</p>
                    @endif
                    <p class="mt-3 text-xs muted">
                        {{ trans_choice('{0}занятий пока нет|{1}:count занятие|[2,4]:count занятия|[5,*]:count занятий', $subject->lessons->count(), ['count' => $subject->lessons->count()]) }}
                    </p>
                </div>
            @empty
                <x-empty-state :title="__('Предметов пока нет')"/>
            @endforelse
        </div>

        <aside class="space-y-4">
            @if ($direction->teacher)
                <div class="surface p-5">
                    <h2 class="mb-2 text-sm font-semibold">{{ __('Ведёт') }}</h2>
                    <p>{{ $direction->teacher->fullName() }}</p>
                    @if ($direction->teacher->bio)
                        <p class="mt-1 text-sm muted">{{ $direction->teacher->bio }}</p>
                    @endif
                </div>
            @endif

            @if ($direction->groups->isNotEmpty())
                <div class="surface p-5">
                    <h2 class="mb-3 text-sm font-semibold">{{ __('Группы и расписание') }}</h2>
                    <ul class="space-y-3 text-sm">
                        @foreach ($direction->groups as $group)
                            <li>
                                <span class="font-medium">{{ $group->name }}</span>
                                <ul class="mt-1 space-y-0.5 text-xs muted">
                                    @forelse ($group->schedules as $slot)
                                        <li>{{ App\Support\Week::name($slot->day_of_week) }}, {{ substr($slot->starts_at, 0, 5) }}–{{ substr($slot->ends_at, 0, 5) }}</li>
                                    @empty
                                        <li>{{ __('расписание уточняется') }}</li>
                                    @endforelse
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </aside>
    </div>
@endsection
