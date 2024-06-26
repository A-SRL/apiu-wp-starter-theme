import path from 'path';
import { defineConfig } from 'vite';
import fs from "fs";

const ROOT = path.resolve('../../../')
const BASE = __dirname.replace(ROOT, '');

export default defineConfig({
    base: process.env.NODE_ENV === 'production' ? `${BASE}/dist/` : BASE,
    build: {
        manifest: true,
        assetsDir: '.',
        outDir: `dist`,
        emptyOutDir: true,
        sourcemap: true,
        rollupOptions: {
            input: [
                'resources/styles/app.css',
                'resources/scripts/app.js',
            ],
            output: {
                entryFileNames: '[hash].js',
                assetFileNames: '[hash].[ext]',
            },
        },
    },
    plugins: [
        {
            name: 'avt-vite-plugin',
            configureServer(server) {
                let hotFile = __dirname+'/hot';
                fs.writeFileSync(hotFile, 'hot');
                process.on("exit", function () {
                    console.log('Exiting...');
                    if (fs.existsSync(hotFile)) {
                        fs.rmSync(hotFile);
                    }
                });
                process.on("SIGINT", () => process.exit());
                process.on("SIGTERM", () => process.exit());
                process.on("SIGHUP", () => process.exit());
            },
            handleHotUpdate({ file, server }) {
                if (file.endsWith('.php') || file.endsWith('.css')) {
                    server.ws.send({ type: 'full-reload' });
                }
            },
    },
    ],
});
