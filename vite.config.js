import {defineConfig} from 'vite';
import { resolve, join } from 'path';
import { ViteMinifyPlugin } from 'vite-plugin-minify'
import { viteStaticCopy } from 'vite-plugin-static-copy'
import alias from '@rollup/plugin-alias'

export default defineConfig({
    plugins: [
        alias(),
        viteStaticCopy({
            targets: [
                {
                    src: 'App/assets/fonts/*',
                    dest: 'fonts'
                },
                {
                    src: 'App/assets/images/*',
                    dest: 'images'
                },
            ],
        }),
        ViteMinifyPlugin({}),
    ],
    emptyOutDir: true,
    root: 'src', // Set the root directory for Vite
    build: {
        outDir: '../public', // Output directory for compiled assets
        rollupOptions: {
            input: {
                main: '/App/assets/js/index.js', // Main JavaScript entry point
                style: '/App/assets/scss/index.scss', // Main CSS entry point
            },
            output: {
                manualChunks: undefined,
                entryFileNames: "js/app.js",
                assetFileNames: "css/app.css",
            },
        },
    },
    optimizeDeps: { force: true, },
    resolve: {
        alias: [
            {
                find: /@(.*)/, replacement: join(resolve(__dirname, 'src/App/assets/'), "$1")
            },
            {
                // this is required for the SCSS modules
                find: /^~(.*)$/,
                replacement: '$1',
            }
        ],
    },

});
