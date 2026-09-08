@extends('layouts.app')
@section('title', __('Панель управления').' — ItCube')

@section('content')
    <x-page-header :title="__('Панель управления')"
                   :subtitle="__('Не сводка активности, а ответ на три вопроса: кто учится, как ходят и что не получается')"/>

    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat :label="__('Учеников')" :value="$headcount['students']"/>
        <x-stat :label="__('Групп')" :value="$headcount['groups']"/>
        <x-stat :label="__('Преподавателей')" :value="$headcount['teachers']"/>
        <x-stat :label="__('Родителей')" :value="$headcount['guardians']" :hint="__('видят своих детей, но не их код')"/>
    </div>

    <div class="mb-8 grid gap-6 lg:grid-cols-2">
        <div class="surface p-6">
            <h2 class="mb-1 font-semibold">{{ __('Посещаемость по неделям') }}</h2>
            <p class="mb-4 text-xs muted">{{ __('Пунктир — на неделе не было занятий') }}</p>
            <x-bar-chart :series="$attendanceTrend->map(fn ($w) => [
                'label' => $w['week']->translatedFormat('j.m'),
                'value' => $w['share'],
            ])->all()" suffix="%"/>
        </div>

        <div class="surface p-6">
            <h2 class="mb-1 font-semibold">{{ __('Сдачи работ по дням') }}</h2>
            <p class="mb-4 text-xs muted">{{ __('За две недели') }}</p>
            <x-bar-chart :series="$submissions->map(fn ($d) => [
                'label' => $d['day']->translatedFormat('j.m'),
                'value' => $d['total'],
            ])->all()"/>
        </div>
    </div>

    <div class="mb-8 grid gap-6 lg:grid-cols-3">
        <div class="surface p-6 lg:col-span-2">
            <h2 class="mb-1 font-semibold">{{ __('Что не получается') }}</h2>
            <p class="mb-4 text-xs muted">
                {{ __('Задачи по доле решивших, снизу вверх. Эта таблица показывает не активность, а тему, которую стоит переобъяснить.') }}
            </p>

            @if ($hardest->isEmpty())
                <p class="py-6 text-center text-sm muted">{{ __('Работ ещё не сдавали') }}</p>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left" style="border-color: var(--line)">
                            <th class="pb-2 font-medium">{{ __('Задача') }}</th>
                            <th class="pb-2 text-right font-medium">{{ __('Попыток') }}</th>
                            <th class="w-40 pb-2 pl-4 font-medium">{{ __('Решили') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hardest as $row)
                            <tr class="border-b last:border-0" style="border-color: var(--line)">
                                <td class="py-3">
                                    <span class="font-medium">{{ $row['title'] }}</span>
                                    <span class="ml-2 font-mono text-xs muted">{{ $row['language'] }}</span>
                                    <p class="text-xs muted">{{ $row['lesson'] }}</p>
                                </td>
                                <td class="py-3 text-right tabular-nums">
                                    {{ $row['attempts_per_student'] }}
                                    <span class="text-xs muted">{{ __('на ученика') }}</span>
                                </td>
                                <td class="py-3 pl-4">
                                    <x-share-bar :share="$row['share']" :label="$row['solved'].'/'.$row['students']"/>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="surface p-6">
            <h2 class="mb-4 font-semibold">{{ __('Посещаемость по группам') }}</h2>
            <div class="space-y-3">
                @forelse ($attendanceByGroup as $row)
                    <div>
                        <div class="mb-1 flex justify-between text-sm">
                            <span>{{ $row['group'] }}</span>
                            <span class="text-xs muted">{{ $row['attended'] }}/{{ $row['total'] }}</span>
                        </div>
                        <x-share-bar :share="$row['share']"/>
                    </div>
                @empty
                    <p class="text-sm muted">{{ __('Отметок пока нет') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="surface p-6">
            <h2 class="mb-4 font-semibold">{{ __('Вопросы, на которых чаще ошибаются') }}</h2>
            @forelse ($hardestQuestions as $row)
                <div class="mb-3 last:mb-0">
                    <p class="text-sm">{{ $row['text'] }}</p>
                    <p class="mb-1 text-xs muted">{{ $row['quiz'] }}</p>
                    <x-share-bar :share="$row['share']" :label="$row['correct'].'/'.$row['answers']"/>
                </div>
            @empty
                <p class="text-sm muted">{{ __('Тесты ещё не проходили') }}</p>
            @endforelse
        </div>

        <div class="surface p-6">
            <h2 class="mb-4 font-semibold">{{ __('На каких языках пишут') }}</h2>
            @php($totalSubs = max(1, $languages->sum('total')))
            @forelse ($languages as $row)
                <div class="mb-3 last:mb-0">
                    <div class="mb-1 flex justify-between text-sm">
                        <span class="font-mono">{{ $row['language'] }}</span>
                        <span class="text-xs muted">{{ $row['total'] }}</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full" style="background: var(--surface-sunken)">
                        <div class="h-full rounded-full bg-brand-500" style="width: {{ max(2, (int) round($row['total'] / $totalSubs * 100)) }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm muted">{{ __('Работ ещё не сдавали') }}</p>
            @endforelse
        </div>
    </div>
@endsection
