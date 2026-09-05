@props(['title', 'subtitle' => null, 'back' => null])

<div class="mb-8">
    @if ($back)
        <a href="{{ $back }}" class="mb-2 inline-flex items-center gap-1.5 text-sm muted hover:underline">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            {{ $slot->isEmpty() ? __('Назад') : $slot }}
        </a>
    @endif
    <h1 class="text-3xl font-bold">{{ $title }}</h1>
    @if ($subtitle)
        <p class="mt-2 muted">{{ $subtitle }}</p>
    @endif
</div>
