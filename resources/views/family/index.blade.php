@extends('layouts.app')
@section('title', __('Мои дети').' — ItCube')

@section('content')
    <x-page-header :title="__('Мои дети')"
                   :subtitle="__('Посещаемость и результаты. Код ребёнка здесь не показывается: разбор решения — дело преподавателя.')"/>

    @forelse ($children as $row)
        @php($child = $row['child'])
        <a href="{{ route('family.children.show', $child) }}" class="surface mb-4 block p-6 transition hover:shadow-lg">
            <div class="flex flex-wrap items-start gap-4">
                <div class="min-w-0 flex-1">
                    <h2 class="font-semibold">{{ $child->fullName() }}</h2>
                    <p class="mt-1 text-sm muted">
                        @forelse ($child->groups as $group)
                            {{ $group->direction->name }} · {{ $group->name }}@if (! $loop->last), @endif
                        @empty
                            {{ __('пока не записан ни в одну группу') }}
                        @endforelse
                    </p>
                </div>

                <div class="w-40">
                    <p class="mb-1 text-xs muted">{{ __('Посещаемость') }}</p>
                    @if ($row['attendance']['share'] === null)
                        <p class="text-sm muted">{{ __('отметок нет') }}</p>
                    @else
                        <x-share-bar :share="$row['attendance']['share']"/>
                    @endif
                </div>

                <div class="w-32 text-right">
                    <p class="mb-1 text-xs muted">{{ __('Задачи') }}</p>
                    <p class="text-lg font-medium tabular-nums">
                        {{ $row['solved']['solved'] }}<span class="text-sm muted">/{{ $row['solved']['attempted'] }}</span>
                    </p>
                </div>
            </div>
        </a>
    @empty
        <x-empty-state :title="__('К вашей учётной записи ещё не привязан ни один ученик')"
                       :hint="__('Привязку делает администратор центра')"/>
    @endforelse
@endsection
