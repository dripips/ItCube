@extends('layouts.app')
@section('title', ($group->exists ? $group->name : __('Новая группа')).' — ItCube')

@section('content')
    <x-page-header :title="$group->exists ? $group->name : __('Новая группа')"
                   :back="route('admin.groups.index')">{{ __('Группы') }}</x-page-header>

    <form method="POST" action="{{ $group->exists ? route('admin.groups.update', $group) : route('admin.groups.store') }}"
          class="grid gap-6 lg:grid-cols-3">
        @csrf
        @if ($group->exists) @method('PATCH') @endif

        <div class="space-y-6 lg:col-span-2">
            <div class="surface grid gap-4 p-6 sm:grid-cols-2">
                <label class="block text-sm">
                    <span class="mb-1.5 block font-medium">{{ __('Название') }}</span>
                    <input name="name" value="{{ old('name', $group->name) }}" required class="field">
                    @error('name')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                </label>
                <label class="block text-sm">
                    <span class="mb-1.5 block font-medium">{{ __('Направление') }}</span>
                    <select name="direction_id" class="field">
                        @foreach ($directions as $direction)
                            <option value="{{ $direction->id }}" @selected(old('direction_id', $group->direction_id) == $direction->id)>{{ $direction->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block text-sm">
                    <span class="mb-1.5 block font-medium">{{ __('Ведёт') }}</span>
                    <select name="teacher_id" class="field">
                        <option value="">{{ __('не назначен') }}</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('teacher_id', $group->teacher_id) == $teacher->id)>{{ $teacher->fullName() }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="block text-sm">
                        <span class="mb-1.5 block font-medium">{{ __('Начало') }}</span>
                        <input type="date" name="starts_on" value="{{ old('starts_on', $group->starts_on?->toDateString()) }}" class="field">
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1.5 block font-medium">{{ __('Конец') }}</span>
                        <input type="date" name="ends_on" value="{{ old('ends_on', $group->ends_on?->toDateString()) }}" class="field">
                        @error('ends_on')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                    </label>
                </div>
            </div>

            <div class="surface p-6">
                <h2 class="mb-1 text-sm font-semibold">{{ __('Расписание') }}</h2>
                <p class="mb-4 text-xs muted">{{ __('Пустые строки не сохраняются') }}</p>

                @php($slots = old('schedule', $group->schedules->map(fn ($s) => [
                    'day_of_week' => $s->day_of_week, 'starts_at' => substr($s->starts_at, 0, 5),
                    'ends_at' => substr($s->ends_at, 0, 5), 'room' => $s->room,
                ])->all()))

                <div class="space-y-2">
                    @for ($i = 0; $i < 4; $i++)
                        @php($slot = $slots[$i] ?? null)
                        <div class="grid grid-cols-4 gap-2">
                            <select name="schedule[{{ $i }}][day_of_week]" class="field">
                                @foreach (App\Support\Week::DAYS as $day)
                                    <option value="{{ $day }}" @selected(($slot['day_of_week'] ?? 1) == $day)>{{ App\Support\Week::name($day) }}</option>
                                @endforeach
                            </select>
                            <input type="time" name="schedule[{{ $i }}][starts_at]" value="{{ $slot['starts_at'] ?? '' }}" class="field">
                            <input type="time" name="schedule[{{ $i }}][ends_at]" value="{{ $slot['ends_at'] ?? '' }}" class="field">
                            <input name="schedule[{{ $i }}][room]" value="{{ $slot['room'] ?? '' }}" class="field" placeholder="{{ __('кабинет') }}">
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="surface p-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_archived" value="1" @checked(old('is_archived', $group->is_archived)) class="rounded">
                    {{ __('В архиве') }}
                </label>
            </div>

            <div class="surface p-6">
                <h2 class="mb-3 text-sm font-semibold">{{ __('Ученики') }}</h2>
                <div class="max-h-96 space-y-2 overflow-y-auto">
                    @foreach ($students as $student)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="students[]" value="{{ $student->id }}"
                                   @checked($group->exists && $group->students->contains($student)) class="rounded">
                            {{ $student->fullName() }}
                        </label>
                    @endforeach
                </div>
            </div>

            <button class="btn-primary w-full">{{ __('Сохранить') }}</button>
        </div>
    </form>
@endsection
