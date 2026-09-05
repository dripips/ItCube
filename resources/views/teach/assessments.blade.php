@extends('layouts.app')
@section('title', __('Контрольные').' — ItCube')

@section('content')
    <x-page-header :title="__('Контрольные')"/>

    @forelse ($assessments as $assessment)
        <a href="{{ route('teach.assessments.show', $assessment) }}" class="surface mb-3 flex flex-wrap items-center gap-4 p-5 transition hover:shadow-lg">
            <div class="min-w-0 flex-1">
                <h2 class="font-semibold">{{ $assessment->title }}</h2>
                <p class="mt-0.5 text-sm muted">{{ $assessment->group->direction->name }} · {{ $assessment->group->name }}</p>
            </div>
            <span class="badge-neutral">{{ trans_choice('{0}работ нет|{1}:count работа|[2,4]:count работы|[5,*]:count работ', $assessment->attempts_count, ['count' => $assessment->attempts_count]) }}</span>
            <span class="{{ $assessment->isOpen() ? 'badge-brand' : 'badge' }}">{{ $assessment->isOpen() ? __('идёт') : __('закрыта') }}</span>
        </a>
    @empty
        <x-empty-state :title="__('Контрольных пока нет')"/>
    @endforelse
@endsection
