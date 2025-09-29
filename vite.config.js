import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
     server: {
        host: '0.0.0.0',   // écouter sur toutes les interfaces
        port: 5173,        // tu peux changer si besoin
        hmr: {
            host: '192.168.1.222', // ⚡ mets l'IP locale de ton PC ici
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
