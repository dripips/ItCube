@extends('layouts.app')
@section('title', ($person->exists ? $person->fullName() : __('Новый человек')).' — ItCube')

@section('content')
    <x-page-header :title="$person->exists ? $person->fullName() : __('Новый человек')"
                   :back="route('admin.people.index')">{{ __('Люди') }}</x-page-header>

    <form method="POST" action="{{ $person->exists ? route('admin.people.update', $person) : route('admin.people.store') }}"
          class="grid gap-6 lg:grid-cols-3">
        @csrf
        @if ($person->exists) @method('PATCH') @endif

        <div class="surface space-y-4 p-6 lg:col-span-2">
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm">
                    <span class="mb-1.5 block font-medium">{{ __('Фамилия') }}</span>
                    <input name="last_name" value="{{ old('last_name', $person->last_name) }}" required class="field">
                    @error('last_name')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                </label>
                <label class="block text-sm">
                    <span class="mb-1.5 block font-medium">{{ __('Имя') }}</span>
                    <input name="first_name" value="{{ old('first_name', $person->first_name) }}" required class="field">
                    @error('first_name')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                </label>
                <label class="block text-sm">
                    <span class="mb-1.5 block font-medium">{{ __('Логин') }}</span>
                    <input name="username" value="{{ old('username', $person->username) }}" required class="field font-mono">
                    @error('username')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                </label>
                <label class="block text-sm">
                    <span class="mb-1.5 block font-medium">{{ __('Почта') }}</span>
                    <input name="email" type="email" value="{{ old('email', $person->email) }}" class="field">
                    <span class="mt-1 block text-xs muted">{{ __('Необязательна: у младших групп её обычно нет') }}</span>
                    @error('email')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                </label>
            </div>

            <label class="block text-sm">
                <span class="mb-1.5 block font-medium">{{ __('О человеке') }}</span>
                <textarea name="bio" rows="3" class="field">{{ old('bio', $person->bio) }}</textarea>
            </label>

            <label class="block text-sm">
                <span class="mb-1.5 block font-medium">{{ __('Пароль') }}</span>
                <input name="password" type="password" class="field" autocomplete="new-password">
                <span class="mt-1 block text-xs muted">
                    {{ $person->exists ? __('Пустое поле — пароль не меняется') : __('Пустое поле — будет выдан пароль password') }}
                </span>
                @error('password')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
            </label>
        </div>

        <div class="space-y-6">
            <div class="surface space-y-4 p-6">
                <label class="block text-sm">
                    <span class="mb-1.5 block font-medium">{{ __('Роль') }}</span>
                    <select name="role" class="field">
                        @foreach (App\Enums\Role::assignable() as $option)
                            <option value="{{ $option->value }}" @selected(old('role', $person->role?->value) === $option->value)>
                                {{ $option->label() }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $person->exists ? $person->is_active : true)) class="rounded">
                    {{ __('Учётная запись работает') }}
                </label>
            </div>

            @if ($person->exists && $person->isStudent())
                <div class="surface p-6">
                    <h2 class="mb-3 text-sm font-semibold">{{ __('Группы') }}</h2>
                    <div class="space-y-2">
                        @foreach ($groups as $group)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="groups[]" value="{{ $group->id }}"
                                       @checked($person->groups->contains($group)) class="rounded">
                                {{ $group->name }} <span class="text-xs muted">{{ $group->direction->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($person->exists && $person->isGuardian())
                <div class="surface p-6">
                    <h2 class="mb-1 text-sm font-semibold">{{ __('Дети') }}</h2>
                    <p class="mb-3 text-xs muted">{{ __('Взрослый видит посещаемость и результаты этих учеников') }}</p>
                    <div class="max-h-64 space-y-2 overflow-y-auto">
                        @foreach ($students as $student)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="children[]" value="{{ $student->id }}"
                                       @checked($person->children->contains($student)) class="rounded">
                                {{ $student->fullName() }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <button class="btn-primary w-full">{{ __('Сохранить') }}</button>

            @unless ($person->exists)
                <p class="text-xs muted">{{ __('Группы и детей можно будет привязать сразу после сохранения') }}</p>
            @endunless
        </div>
    </form>
@endsection
