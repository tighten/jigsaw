import { defineConfig } from 'vite';
import jigsaw from '@tighten/jigsaw-vite-plugin';

export default defineConfig({
    plugins: [
        jigsaw({
            input: ['source/_assets/js/main.js', 'source/_assets/css/main.css'],
            refresh: true,
        }),
    ],
});
