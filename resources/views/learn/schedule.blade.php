@extends('layouts.app')
@section('title', __('Расписание').' — ItCube')

@section('content')
    <x-page-header :title="__('Расписание')"/>

    @if ($byDay->isEmpty())
        <x-empty-state :title="__('Расписания пока нет')"/>
    @else
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach (App\Support\Week::DAYS as $day)
                @continue ($byDay->get($day) === null)
                <div class="surface p-5">
                    <h2 class="mb-3 font-semibold">{{ App\Support\Week::name($day) }}</h2>
                    <ul class="space-y-3">
                        @foreach ($byDay->get($day) as $slot)
                            <li class="flex gap-3">
                                <span class="font-mono text-sm tabular-nums">{{ substr($slot->starts_at, 0, 5) }}</span>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium">{{ $slot->group->direction->name }}</p>
                                    <p class="text-xs muted">{{ $slot->group->name }}@if ($slot->room) · {{ $slot->room }}@endif</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif
@endsection
