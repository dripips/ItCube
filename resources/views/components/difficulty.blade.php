@props(['level'])

@php($map = [
    'easy' => 'bg-emerald-500/12 text-emerald-700 dark:text-emerald-300',
    'medium' => 'bg-amber-500/12 text-amber-700 dark:text-amber-300',
    'hard' => 'bg-rose-500/12 text-rose-700 dark:text-rose-300',
])

<span class="badge {{ $map[$level->value] ?? '' }}">{{ $level->label() }}</span>
