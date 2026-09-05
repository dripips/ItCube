@extends('layouts.app')
@section('title', __('Контрольные').' — ItCube')

@section('content')
    <x-page-header :title="__('Контрольные')"/>

    @forelse ($assessments as $assessment)
        @php($attempt = $attempts->get($assessment->id))
        <a href="{{ route('learn.assessments.show', $assessment) }}" class="surface mb-3 flex flex-wrap items-center gap-4 p-5 transition hover:shadow-lg">
            <div class="min-w-0 flex-1">
                <h2 class="font-semibold">{{ $assessment->title }}</h2>
                <p class="mt-0.5 text-sm muted">{{ $assessment->group->direction->name }} · {{ $assessment->group->name }}</p>
            </div>

            @if ($attempt?->submitted_at)
                <span class="badge-pass">{{ $attempt->score }}/{{ $attempt->max_score }}</span>
            @elseif ($assessment->isOpen())
                <span class="badge-brand">{{ __('идёт') }}</span>
            @else
                <span class="badge-neutral">{{ __('закрыта') }}</span>
            @endif

            @if ($assessment->closes_at)
                <time class="text-xs muted">{{ $assessment->closes_at->translatedFormat('j F, H:i') }}</time>
            @endif
        </a>
    @empty
        <x-empty-state :title="__('Контрольных пока не было')"/>
    @endforelse
@endsection
