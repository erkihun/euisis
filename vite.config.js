import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.tsx',
            refresh: true,
        }),
        react(),
    ],
    build: {
        chunkSizeWarningLimit: 600,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    // Force all React context providers into one shared chunk.
                    // Without this, Vite may inline a provider into whichever
                    // layout chunk first imports it, then duplicate createContext()
                    // in every page chunk that also imports from it — causing the
                    // "must be used within <Provider>" runtime error.
                    if (id.includes('ConfirmProvider') || id.includes('ToastProvider')) {
                        return 'providers';
                    }
                },
            },
        },
    },
});
