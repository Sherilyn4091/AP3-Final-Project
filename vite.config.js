/*
 * vite.config.js 
 * Vite configuration file
 * 
 * Configures Vite build tool with Laravel and React plugins.
 * Sets up asset input paths and development server watch settings.
 * 
 * @type {import('vite').UserConfig}
 */

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        react(),
        laravel({
            input: [
                'resources/css/style.css',
                'resources/js/script.js',
                'resources/js/app.js',
                'resources/js/admin.jsx',
                'resources/js/admin-pages.js'
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});