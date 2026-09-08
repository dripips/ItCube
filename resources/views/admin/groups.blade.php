@extends('layouts.app')
@section('title', __('Группы').' — ItCube')

@section('content')
    <x-page-header :title="__('Группы')"/>

    <div class="mb-6">
        <a href="{{ route('admin.groups.create') }}" class="btn-primary">{{ __('Завести группу') }}</a>
    </div>

    <div class="surface overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b text-left" style="border-color: var(--line)">
                    <th class="px-5 py-3 font-medium">{{ __('Группа') }}</th>
                    <th class="px-3 py-3 font-medium">{{ __('Направление') }}</th>
                    <th class="px-3 py-3 font-medium">{{ __('Ведёт') }}</th>
                    <th class="px-3 py-3 font-medium">{{ __('Расписание') }}</th>
                    <th class="px-3 py-3 text-right font-medium">{{ __('Учеников') }}</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($groups as $group)
                    <tr class="border-b last:border-0" style="border-color: var(--line)">
                        <td class="px-5 py-3">
                            {{ $group->name }}
                            @if ($group->is_archived)
                                <span class="badge-neutral ml-2">{{ __('в архиве') }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 muted">{{ $group->direction->name }}</td>
                        <td class="px-3 py-3 muted">{{ $group->teacher?->fullName() ?? '—' }}</td>
                        <td class="px-3 py-3 text-xs muted">
                            @forelse ($group->schedules as $slot)
                                {{ App\Support\Week::short($slot->day_of_week) }} {{ substr($slot->starts_at, 0, 5) }}@if (! $loop->last), @endif
                            @empty
                                —
                            @endforelse
                        </td>
                        <td class="px-3 py-3 text-right tabular-nums">{{ $group->students_count }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.groups.edit', $group) }}" class="text-brand-600 hover:underline dark:text-brand-400">{{ __('Правка') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center muted">{{ __('Групп пока нет') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
