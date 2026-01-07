import react from '@vitejs/plugin-react';
import tsconfigPaths from 'vite-tsconfig-paths';
import { defineConfig } from 'vitest/config';

export default defineConfig({
    plugins: [react(), tsconfigPaths()],
    test: {
        environment: 'jsdom',
        root: './',
        include: ['vitest/**/*.test.{js,ts,tsx}'],
        exclude: ['**/node_modules/**'],
        clearMocks: true,
        restoreMocks: true,
        setupFiles: ['dotenv/config', 'vitest/setup.ts'],
        globals: true,
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
