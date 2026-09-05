import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                // Кириллица обязательна: Instrument Sans из заготовки её не
                // покрывает, а платформа русскоязычная.
                bunny('Inter', { weights: [400, 500, 600, 700], subsets: ['latin', 'cyrillic'] }),
                bunny('JetBrains Mono', { weights: [400, 500], subsets: ['latin', 'cyrillic'] }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
