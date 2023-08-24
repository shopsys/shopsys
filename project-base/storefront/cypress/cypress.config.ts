import { defineConfig } from 'cypress';

export default defineConfig({
    viewportWidth: 1920,
    viewportHeight: 1080,
    defaultCommandTimeout: 20000,
    screenshotsFolder: 'screenshots',
    videosFolder: 'videos',
    e2e: {
        baseUrl: 'http://127.0.0.1:8000/',
        specPattern: 'e2e/**/*.cy.ts',
        supportFile: 'support/index.ts',
    },
});
