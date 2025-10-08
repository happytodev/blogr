import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        tailwindcss(),
    ],
    build: {
        outDir: 'resources/dist',
        rollupOptions: {
            input: {
                css: 'resources/css/index.css',
                js: 'resources/js/index.js',
            },
            output: {
                entryFileNames: 'blogr.js',
                assetFileNames: 'blogr.[ext]',
            },
        },
    },
});
