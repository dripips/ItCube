@props(['series', 'suffix' => '', 'height' => 128])

{{-- Столбики рисуются разметкой, а не библиотекой: две диаграммы не окупают
     ни килобайтов в браузере, ни зависимости в сборке. Полоса без значения
     (занятий на неделе не было) показана пунктиром, а не нулём — ноль здесь
     означал бы «никто не пришёл». --}}
@php
    $values = collect($series)->pluck('value')->filter(fn ($v) => $v !== null);
    $max = max(1, $values->max() ?? 1);
@endphp

<div class="flex items-end gap-1.5" style="height: {{ $height }}px">
    @foreach ($series as $point)
        @php($v = $point['value'])
        <div class="group flex h-full flex-1 flex-col justify-end" title="{{ $point['label'] }}: {{ $v === null ? '—' : $v.$suffix }}">
            @if ($v === null)
                <div class="w-full rounded-t border border-dashed" style="height: 6px; border-color: var(--line)"></div>
            @else
                <div class="w-full rounded-t bg-brand-500/70 transition group-hover:bg-brand-500"
                     style="height: {{ max(2, (int) round($v / $max * ($height - 22))) }}px"></div>
            @endif
            <span class="mt-1.5 truncate text-center text-[10px] muted">{{ $point['label'] }}</span>
        </div>
    @endforeach
</div>
