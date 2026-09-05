@props(['active' => false])

<a {{ $attributes->merge([
    'class' => 'rounded-lg px-3 py-1.5 transition '
        .($active ? 'bg-brand-500/12 text-brand-700 dark:text-brand-300 font-medium' : 'hover:bg-[var(--surface-sunken)]'),
]) }}>{{ $slot }}</a>
