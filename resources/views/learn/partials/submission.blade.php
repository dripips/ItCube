@php($results = $submission->test_results ?? [])

<div class="flex flex-wrap items-center gap-3">
    <span class="{{ $submission->passed() ? 'badge-pass' : 'badge-fail' }}">{{ $submission->status->label() }}</span>
    <span class="text-sm muted">{{ __('Пройдено') }} {{ $submission->passed_count }}/{{ $submission->total_count }}</span>
    <span class="text-sm muted">{{ __('Баллы') }} {{ $submission->score }}</span>
    @if ($submission->runtime_ms > 0)
        <span class="ml-auto text-xs muted">{{ number_format($submission->runtime_ms / 1000, 1) }} {{ __('с') }}</span>
    @endif
</div>

@if ($results === [])
    <pre class="code mt-4 overflow-x-auto p-4 text-sm">{{ $submission->output }}</pre>
@else
    <div class="mt-4 space-y-2">
        @foreach ($results as $row)
            <div class="rounded-lg border p-3 text-sm" style="border-color: var(--line)">
                <div class="flex items-center gap-2">
                    <span class="{{ $row['passed'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        {{ $row['passed'] ? '✓' : '✗' }}
                    </span>
                    <span class="font-medium">{{ $row['name'] }}</span>
                    @if ($row['hidden'])
                        <span class="badge-neutral">{{ __('скрытый') }}</span>
                    @endif
                    <span class="ml-auto text-xs muted">{{ $row['points'] }}/{{ $row['max_points'] }}</span>
                </div>

                @if (! $row['hidden'] && ! $row['passed'])
                    <div class="mt-2 grid gap-1 font-mono text-xs">
                        @if (filled($row['stdin'] ?? null))
                            <p><span class="muted">{{ __('вход') }}:</span> {{ $row['stdin'] }}</p>
                        @endif
                        <p><span class="muted">{{ __('ждали') }}:</span> {{ $row['expected'] }}</p>
                        <p><span class="muted">{{ __('получили') }}:</span> {{ $row['actual'] === '' ? '—' : $row['actual'] }}</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
