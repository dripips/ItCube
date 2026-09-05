{{-- Знак: три грани куба тремя оттенками одного цвета. Ни картинки, ни
     шрифта иконок — знак должен рисоваться до загрузки чего бы то ни было. --}}
<svg {{ $attributes->merge(['viewBox' => '0 0 32 32', 'fill' => 'none', 'aria-hidden' => 'true']) }}>
    <path d="M16 3 28 9.5v13L16 29 4 22.5v-13L16 3Z" class="fill-brand-600"/>
    <path d="M16 3 28 9.5 16 16 4 9.5 16 3Z" class="fill-brand-400"/>
    <path d="M16 16v13L4 22.5v-13L16 16Z" class="fill-brand-800"/>
</svg>
