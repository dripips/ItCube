@extends('layouts.app')
@section('title', $assessment->title.' — ItCube')

@section('content')
    <x-page-header :title="$assessment->title"
                   :subtitle="$assessment->description"
                   :back="route('learn.assessments')">{{ __('Контрольные') }}</x-page-header>

    <div class="surface mb-6 flex flex-wrap items-center gap-4 p-5 text-sm">
        <span class="{{ $assessment->isOpen() ? 'badge-brand' : 'badge' }}">
            {{ $assessment->isOpen() ? __('идёт') : __('закрыта') }}
        </span>
        @if ($assessment->closes_at)
            <span class="muted">{{ __('до') }} {{ $assessment->closes_at->translatedFormat('j F, H:i') }}</span>
        @endif
        @if ($assessment->duration_minutes)
            <span class="muted">{{ __('на работу') }} {{ $assessment->duration_minutes }} {{ __('мин') }}</span>
        @endif
        <span class="ml-auto muted">{{ __('Максимум') }} {{ $assessment->maxScore() }} {{ __('баллов') }}</span>
    </div>

    <div class="space-y-3">
        @foreach ($assessment->items as $item)
            @php($work = $item->itemable)
            @continue ($work === null)
            <div class="surface flex flex-wrap items-center gap-4 p-5">
                <div class="min-w-0 flex-1">
                    <p class="font-medium">{{ $work->title }}</p>
                    <p class="mt-0.5 text-xs muted">
                        {{ $item->itemable_type === App\Models\Quiz::class ? __('Тест') : __('Задача с кодом') }}
                    </p>
                </div>
                <span class="badge-neutral">{{ $item->points }} {{ __('баллов') }}</span>
                <a class="btn-ghost"
                   href="{{ $item->itemable_type === App\Models\Quiz::class
                        ? route('learn.quizzes.show', $work)
                        : route('learn.assignments.show', $work) }}">{{ __('Открыть') }}</a>
            </div>
        @endforeach
    </div>
@endsection
