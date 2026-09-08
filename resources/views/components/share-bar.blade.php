@props(['share', 'label' => null])

<div class="flex items-center gap-3">
    <div class="h-2 flex-1 overflow-hidden rounded-full" style="background: var(--surface-sunken)">
        <div class="h-full rounded-full {{ $share >= 85 ? 'bg-emerald-500' : ($share >= 70 ? 'bg-amber-500' : 'bg-rose-500') }}"
             style="width: {{ max(2, $share) }}%"></div>
    </div>
    <span class="w-12 text-right text-sm tabular-nums">{{ $label ?? $share.'%' }}</span>
</div>
