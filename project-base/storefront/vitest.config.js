import react from '@vitejs/plugin-react';
import tsconfigPaths from 'vite-tsconfig-paths';
import { defineConfig } from 'vitest/config';

export default defineConfig({
    plugins: [react(), tsconfigPaths()],
    test: {
        environment: 'jsdom',
        rootDir: './',
        testMatch: ['vitest/**/*.test.js'],
        clearMocks: true,
        restoreMocks: true,
        setupFiles: 'dotenv/config',
    },
    resolve: {
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
