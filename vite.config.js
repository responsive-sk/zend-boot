import {defineConfig} from 'vite';
import { ViteMinifyPlugin } from 'vite-plugin-minify'
import { viteStaticCopy } from 'vite-plugin-static-copy'
import alias from '@rollup/plugin-alias'
import path from 'path'
import commonjs from "vite-plugin-commonjs";
export default defineConfig({
    plugins: [
        alias(), commonjs(),
        viteStaticCopy({
            targets: [
                {
                    src: 'App/assets/fonts/*',
                    dest: 'fonts'
                },
                {
                    src: 'App/assets/images/*',
                    dest: 'images/app/'
                },
            ],
        }),
        ViteMinifyPlugin({}),
    ],
    emptyOutDir: true,
    root: path.resolve(__dirname, 'src'), //'src', // Set the root directory for Vite
    build: {
        commonjsOptions: { transformMixedEsModules: true }, // Change
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
                // this is required for the SCSS modules
                find: /^~(.*)$/,
                replacement: '$1',
            }
        ],
    },
});
