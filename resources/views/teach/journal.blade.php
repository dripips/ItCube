@extends('layouts.app')
@section('title', __('Журнал').' — ItCube')

@section('content')
    <x-page-header :title="__('Журнал посещаемости')"/>

    <form method="GET" class="surface mb-6 flex flex-wrap items-end gap-4 p-5">
        <label class="text-sm">
            <span class="mb-1.5 block font-medium">{{ __('Группа') }}</span>
            <select name="group" class="field w-auto" onchange="this.form.submit()">
                @foreach ($groups as $option)
                    <option value="{{ $option->slug }}" @selected($group && $option->is($group))>
                        {{ $option->name }} — {{ $option->direction->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label class="text-sm">
            <span class="mb-1.5 block font-medium">{{ __('Дата') }}</span>
            <input type="date" name="date" value="{{ $date->toDateString() }}" class="field w-auto" onchange="this.form.submit()">
        </label>

        <noscript><button class="btn-ghost">{{ __('Показать') }}</button></noscript>
    </form>

    @if ($group === null)
        <x-empty-state :title="__('Групп пока нет')"/>
    @elseif ($students->isEmpty())
        <x-empty-state :title="__('В группе пока никого нет')"/>
    @else
        <form method="POST" action="{{ route('teach.journal.store') }}">
            @csrf
            <input type="hidden" name="group" value="{{ $group->slug }}">
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">

            <div class="surface overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left" style="border-color: var(--line)">
                            <th class="px-5 py-3 font-medium">{{ __('Ученик') }}</th>
                            @foreach (App\Enums\AttendanceStatus::cases() as $status)
                                <th class="px-3 py-3 text-center font-medium">{{ $status->label() }}</th>
                            @endforeach
                            <th class="px-5 py-3 text-right font-medium">{{ __('Посещаемость') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            @php($mark = $marks->get($student->id))
                            @php($stat = $stats->get($student->id))
                            <tr class="border-b last:border-0" style="border-color: var(--line)">
                                <td class="px-5 py-3">{{ $student->fullName() }}</td>

                                @foreach (App\Enums\AttendanceStatus::cases() as $status)
                                    <td class="px-3 py-3 text-center">
                                        <input type="radio"
                                               name="marks[{{ $student->id }}]"
                                               value="{{ $status->value }}"
                                               @checked($mark?->status === $status)
                                               aria-label="{{ $student->fullName() }}: {{ $status->label() }}">
                                    </td>
                                @endforeach

                                <td class="px-5 py-3 text-right tabular-nums muted">
                                    @if ($stat && $stat['total'] > 0)
                                        {{ round($stat['attended'] / $stat['total'] * 100) }}%
                                        <span class="text-xs">({{ $stat['attended'] }}/{{ $stat['total'] }})</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button class="btn-primary mt-4">{{ __('Сохранить') }}</button>
        </form>
    @endif
@endsection
