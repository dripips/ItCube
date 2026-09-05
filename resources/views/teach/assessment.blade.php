@extends('layouts.app')
@section('title', $assessment->title.' — ItCube')

@section('content')
    <x-page-header :title="$assessment->title"
                   :subtitle="$assessment->group->direction->name.' · '.$assessment->group->name"
                   :back="route('teach.assessments.index')">{{ __('Контрольные') }}</x-page-header>

    @if ($sheet['rows']->isEmpty())
        <x-empty-state :title="__('В группе пока никого нет')"/>
    @else
        <div class="surface overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left" style="border-color: var(--line)">
                        <th class="px-5 py-3 font-medium">{{ __('Ученик') }}</th>
                        @foreach ($sheet['items'] as $item)
                            <th class="px-3 py-3 text-center font-medium">
                                <span class="block max-w-44 truncate" title="{{ $item->itemable?->title }}">{{ $item->itemable?->title }}</span>
                                <span class="text-xs font-normal muted">{{ $item->points }} {{ __('б.') }}</span>
                            </th>
                        @endforeach
                        <th class="px-5 py-3 text-right font-medium">{{ __('Итог') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sheet['rows'] as $row)
                        <tr class="border-b last:border-0" style="border-color: var(--line)">
                            <td class="px-5 py-3">{{ $row['student']->fullName() }}</td>

                            @foreach ($row['cells'] as $cell)
                                <td class="px-3 py-3 text-center tabular-nums">
                                    @if ($cell['points'] === null)
                                        <span class="muted">—</span>
                                    @else
                                        <span class="{{ $cell['points'] === $cell['max_points'] ? 'text-emerald-600 dark:text-emerald-400' : '' }}">
                                            {{ $cell['points'] }}
                                        </span>
                                        <span class="text-xs muted">({{ $cell['raw'] }}/{{ $cell['out_of'] }})</span>
                                    @endif
                                </td>
                            @endforeach

                            <td class="px-5 py-3 text-right">
                                <span class="font-medium tabular-nums">{{ $row['earned'] }}/{{ $sheet['max'] }}</span>
                                <span class="ml-2 text-xs muted">{{ $row['share'] }}%</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="mt-3 text-xs muted">
            {{ __('Баллы считаются по лучшей попытке: решивший со второго раза решил. Снижать за число попыток нельзя — иначе выгоднее не пробовать.') }}
        </p>
    @endif
@endsection
