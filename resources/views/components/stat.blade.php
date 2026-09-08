@props(['label', 'value', 'hint' => null])

<div class="surface p-5">
    <p class="text-sm muted">{{ $label }}</p>
    <p class="mt-1 text-3xl font-bold tabular-nums">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs muted">{{ $hint }}</p>
    @endif
</div>
