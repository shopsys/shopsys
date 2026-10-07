import react from '@vitejs/plugin-react';
import { defineConfig } from 'vitest/config';

export default defineConfig({
    plugins: [react()],
    test: {
        environment: 'jsdom',
        root: './',
        include: ['vitest/**/*.test.{js,ts,tsx}'],
        exclude: ['**/node_modules/**'],
        watchExclude: ['**/node_modules/**', '**/.git/**', '**/dist/**', '**/build/**', '**/.next/**'],
        clearMocks: true,
        restoreMocks: true,
        setupFiles: ['vitest/setup.ts'],
        globals: true,
    },
    server: {
        watch: {
            ignored: [
                '**/node_modules/**',
                '**/.git/**',
                '**/.next/**',
                '**/.pnpm-store/**',
                '**/cypress/**',
                '**/public/**',
                '**/certificates/**',
                '**/*.log',
            ],
        },
    },
    resolve: {
        tsconfigPaths: true,
        moduleDirectories: [
            'node_modules',
            'components',
            'connectors',
            'graphql',
            'helpers',
            'hooks',
            'pages',
            'store',
            'styles',
            'typeHelpers',
            'types',
            'urql',
        ],
    },
});
