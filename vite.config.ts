import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { existsSync } from 'node:fs';
import { join } from 'node:path';
import { defineConfig } from 'vite';

// Wayfinder requires PHP/Composer – skip gracefully when vendor/ is absent
const hasVendor = existsSync(join(__dirname, 'vendor'));

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        ...(hasVendor ? [wayfinder({ formVariants: true })] : []),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
