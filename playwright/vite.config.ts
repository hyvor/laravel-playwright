import { resolve } from 'path'
import { defineConfig } from 'vite'
import dts from 'vite-plugin-dts'

export default defineConfig({
    build: {
        lib: {
            entry: resolve(__dirname, 'src/index.ts'),
            name: 'laravel-playwright',
           // fileName: 'laravel-playwright',
        },
        rollupOptions: {
            // make sure to externalize deps that shouldn't be bundled
            // into your library
            external: ['@playwright/test'],
            // output: {
            //     // Provide global variables to use in the UMD build
            //     // for externalized deps
            //     globals: {
            //         vue: 'Vue',
            //     },
            // },
        },
    },
    plugins: [dts()]
})