@extends('layouts.app')
@section('title', __('Направления').' — ItCube')

@section('content')
    <x-page-header :title="__('Направления')" :subtitle="__('Каждое направление — набор предметов и групп со своим расписанием')"/>

    @if ($directions->isEmpty())
        <x-empty-state :title="__('Направлений пока нет')"/>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($directions as $direction)
                <a href="{{ route('directions.show', $direction) }}" class="surface group p-5 transition hover:-translate-y-0.5 hover:shadow-lg">
                    <x-cube class="h-8 w-8"/>
                    <h2 class="mt-4 font-semibold group-hover:text-brand-600 dark:group-hover:text-brand-400">{{ $direction->name }}</h2>
                    @if ($direction->age_range)
                        <span class="mt-1 text-xs muted">{{ $direction->age_range }} {{ __('лет') }}</span>
                    @endif
                    <p class="mt-2 line-clamp-3 text-sm muted">{{ $direction->description }}</p>
                </a>
            @endforeach
        </div>
    @endif
@endsection
