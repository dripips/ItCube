@extends('layouts.app')
@section('title', __('Люди').' — ItCube')

@section('content')
    <x-page-header :title="__('Люди')"/>

    <div class="mb-6 flex flex-wrap items-center gap-3">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.people.index') }}" class="{{ $role === null ? 'badge-brand' : 'badge-neutral' }}">
                {{ __('Все') }}
            </a>
            @foreach (App\Enums\Role::assignable() as $option)
                <a href="{{ route('admin.people.index', ['role' => $option->value]) }}"
                   class="{{ $role === $option ? 'badge-brand' : 'badge-neutral' }}">
                    {{ $option->label() }}
                    <span class="opacity-60">{{ $counts[$option->value] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        <form method="GET" class="ml-auto flex gap-2">
            @if ($role)<input type="hidden" name="role" value="{{ $role->value }}">@endif
            <input type="search" name="q" value="{{ request('q') }}" class="field w-48" placeholder="{{ __('Фамилия или логин') }}">
            <button class="btn-ghost">{{ __('Найти') }}</button>
        </form>

        <a href="{{ route('admin.people.create') }}" class="btn-primary">{{ __('Завести') }}</a>
    </div>

    <div class="surface overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b text-left" style="border-color: var(--line)">
                    <th class="px-5 py-3 font-medium">{{ __('Человек') }}</th>
                    <th class="px-3 py-3 font-medium">{{ __('Логин') }}</th>
                    <th class="px-3 py-3 font-medium">{{ __('Роль') }}</th>
                    <th class="px-3 py-3 text-right font-medium">{{ __('Групп') }}</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($people as $person)
                    <tr class="border-b last:border-0" style="border-color: var(--line)">
                        <td class="px-5 py-3">
                            {{ $person->fullName() }}
                            @unless ($person->is_active)
                                <span class="badge-fail ml-2">{{ __('отключён') }}</span>
                            @endunless
                        </td>
                        <td class="px-3 py-3 font-mono text-xs muted">{{ $person->username }}</td>
                        <td class="px-3 py-3">{{ $person->role->label() }}</td>
                        <td class="px-3 py-3 text-right tabular-nums muted">{{ $person->groups_count ?: '—' }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.people.edit', $person) }}" class="text-brand-600 hover:underline dark:text-brand-400">{{ __('Правка') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center muted">{{ __('Никого не нашлось') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $people->links() }}</div>
@endsection
