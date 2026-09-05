@extends('layouts.app')
@section('title', __('Группы').' — ItCube')

@section('content')
    <x-page-header :title="__('Группы')" :subtitle="__('Группы, которые вы ведёте')"/>

    @forelse ($groups as $group)
        <a href="{{ route('teach.groups.show', $group) }}" class="surface mb-3 flex flex-wrap items-center gap-4 p-5 transition hover:shadow-lg">
            <div class="min-w-0 flex-1">
                <h2 class="font-semibold">{{ $group->name }}</h2>
                <p class="mt-0.5 text-sm muted">{{ $group->direction->name }}</p>
            </div>

            <span class="badge-neutral">{{ trans_choice('{0}никого|{1}:count ученик|[2,4]:count ученика|[5,*]:count учеников', $group->students_count, ['count' => $group->students_count]) }}</span>

            <div class="text-xs muted">
                @foreach ($group->schedules as $slot)
                    <div>{{ App\Support\Week::short($slot->day_of_week) }} {{ substr($slot->starts_at, 0, 5) }}</div>
                @endforeach
            </div>
        </a>
    @empty
        <x-empty-state :title="__('Групп пока нет')"
                       :hint="__('Группы заводит администратор — как только вас назначат ведущим, они появятся здесь')"/>
    @endforelse
@endsection
