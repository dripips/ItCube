@extends('layouts.app')
@section('title', $child->fullName().' — ItCube')

@section('content')
    <x-page-header :title="$child->fullName()" :back="route('family.index')">{{ __('Мои дети') }}</x-page-header>

    <div class="mb-8 grid gap-4 sm:grid-cols-3">
        <x-stat :label="__('Посещаемость')"
                :value="$attendance['share'] === null ? '—' : $attendance['share'].'%'"
                :hint="$attendance['attended'].' '.__('из').' '.$attendance['total']"/>
        <x-stat :label="__('Задачи решены')"
                :value="$solved['solved'].' / '.$solved['attempted']"
                :hint="__('из тех, что пробовал')"/>
        <x-stat :label="__('Групп')" :value="$child->groups->count()"/>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="surface p-6">
                <h2 class="mb-4 font-semibold">{{ __('Последние занятия') }}</h2>
                @forelse ($recentAttendance as $mark)
                    <div class="flex items-center gap-3 border-b py-2 last:border-0" style="border-color: var(--line)">
                        <time class="w-24 text-sm tabular-nums muted">{{ $mark->held_on->translatedFormat('j F') }}</time>
                        <span class="flex-1 text-sm">{{ $mark->group->name }}</span>
                        {{-- Три состояния, а не два: «был» зелёным, «опоздал» и
                             «по уважительной» нейтрально, «не был» красным.
                             Опоздание не штрафует посещаемость, но и выглядеть
                             как полное присутствие оно не должно. --}}
                        <span class="{{ match ($mark->status) {
                            App\Enums\AttendanceStatus::Present => 'badge-pass',
                            App\Enums\AttendanceStatus::Absent => 'badge-fail',
                            default => 'badge-neutral',
                        } }}">{{ $mark->status->label() }}</span>
                    </div>
                @empty
                    <p class="text-sm muted">{{ __('Отметок пока нет') }}</p>
                @endforelse
            </div>

            <div class="surface p-6">
                <h2 class="mb-1 font-semibold">{{ __('Сданные работы') }}</h2>
                <p class="mb-4 text-xs muted">{{ __('Сколько наборов данных сошлось. Сам код виден преподавателю и ученику.') }}</p>
                @forelse ($submissions as $submission)
                    <div class="flex items-center gap-3 border-b py-2 last:border-0" style="border-color: var(--line)">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm">{{ $submission->assignment->title }}</p>
                            <p class="text-xs muted">{{ $submission->assignment->lesson->title }}</p>
                        </div>
                        <span class="{{ $submission->passed() ? 'badge-pass' : 'badge-fail' }}">
                            {{ $submission->passed_count }}/{{ $submission->total_count }}
                        </span>
                        <time class="w-20 text-right text-xs muted">{{ $submission->created_at->translatedFormat('j F') }}</time>
                    </div>
                @empty
                    <p class="text-sm muted">{{ __('Работ пока не сдавали') }}</p>
                @endforelse
            </div>
        </div>

        <aside class="space-y-6">
            <div class="surface p-6">
                <h2 class="mb-3 text-sm font-semibold">{{ __('Расписание') }}</h2>
                @foreach ($child->groups as $group)
                    <p class="mt-3 text-sm font-medium first:mt-0">{{ $group->name }}</p>
                    <p class="text-xs muted">{{ $group->teacher?->fullName() }}</p>
                    <ul class="mt-1 space-y-0.5 text-xs muted">
                        @forelse ($group->schedules as $slot)
                            <li>{{ App\Support\Week::name($slot->day_of_week) }}, {{ substr($slot->starts_at, 0, 5) }}–{{ substr($slot->ends_at, 0, 5) }}@if ($slot->room) · {{ $slot->room }}@endif</li>
                        @empty
                            <li>{{ __('расписание уточняется') }}</li>
                        @endforelse
                    </ul>
                @endforeach
            </div>

            <div class="surface p-6">
                <h2 class="mb-3 text-sm font-semibold">{{ __('Контрольные') }}</h2>
                @forelse ($assessments as $assessment)
                    @php($attempt = $attempts->get($assessment->id))
                    <div class="mb-3 last:mb-0">
                        <p class="text-sm">{{ $assessment->title }}</p>
                        <div class="mt-1 flex items-center gap-2 text-xs">
                            @if ($attempt?->submitted_at)
                                <span class="badge-pass">{{ $attempt->score }}/{{ $attempt->max_score }}</span>
                            @elseif ($assessment->isOpen())
                                <span class="badge-brand">{{ __('идёт') }}</span>
                            @else
                                <span class="badge-neutral">{{ __('не сдавал') }}</span>
                            @endif
                            @if ($assessment->closes_at)
                                <time class="muted">{{ $assessment->closes_at->translatedFormat('j F') }}</time>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm muted">{{ __('Контрольных пока не было') }}</p>
                @endforelse
            </div>
        </aside>
    </div>
@endsection
