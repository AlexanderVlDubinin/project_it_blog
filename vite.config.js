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
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0', // Разрешает Vite слушать подключения вне контейнера
        port: 5173,
        hmr: {
            host: 'localhost', // Браузер на ПК будет запрашивать обновления с localhost
        },
        watch: {
            usePolling: true, // Гарантирует, что Vite мгновенно увидит изменения файлов в Docker
            interval: 100, // Проверять файлы каждые 100мс (снижает нагрузку на CPU)
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
