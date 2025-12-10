/// <reference types="vitest" />
import { configDefaults } from 'vitest/config'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import ViteYaml from '@modyfi/vite-plugin-yaml'

// https://vitejs.dev/config/
// https://stackoverflow.com/a/74397545/445757
export default defineConfig({
    plugins: [vue(), ViteYaml()],
    // Root directory for assets (when used as a sprinkle in UF6)
    root: 'app/assets/',
    base: '/assets/',
    // Build configuration for library mode (sprinkle)
    build: {
        outDir: '../../public/assets',
        assetsDir: '',
        emptyOutDir: false, // Don't empty - multiple sprinkles may write here
        manifest: true,
        rollupOptions: {
            input: {
                c6admin: 'app/assets/index.ts'
            }
        }
    },
    optimizeDeps: {
        // Pre-bundle limax and its dependencies for optimal performance
        // This improves Vite cold-start time and ensures consistent behavior
        // Note: C6Admin depends on CRUD6 which uses limax (CommonJS module)
        include: ['limax', 'lodash.deburr'],
        // Exclude sprinkles - treat as source code, not prebuilt packages
        exclude: [
            '@ssnukala/sprinkle-c6admin',
            '@ssnukala/sprinkle-crud6',
            '@userfrosting/sprinkle-core',
            '@userfrosting/sprinkle-account',
            '@userfrosting/sprinkle-admin',
            '@userfrosting/theme-pink-cupcake'
        ]
    },
    test: {
        coverage: {
            reportsDirectory: './_meta/_coverage',
            include: ['app/assets/**/*.*'],
            exclude: ['app/assets/tests/**/*.*']
        },
        environment: 'happy-dom',
        exclude: [
            ...configDefaults.exclude,
            './vendor/**/*.*',
        ],
    }
})
