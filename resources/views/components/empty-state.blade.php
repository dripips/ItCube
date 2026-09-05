@props(['title', 'hint' => null])

<div class="surface flex flex-col items-center gap-2 px-6 py-14 text-center">
    <x-cube class="h-10 w-10 opacity-30"/>
    <p class="mt-2 font-medium">{{ $title }}</p>
    @if ($hint)
        <p class="max-w-md text-sm muted">{{ $hint }}</p>
    @endif
    {{ $slot }}
</div>
