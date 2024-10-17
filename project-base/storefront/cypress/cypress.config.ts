import { defineConfig } from 'cypress';
import { Cypress } from 'cypress';
import getCompareSnapshotsPlugin from 'cypress-visual-regression/dist/plugin';

export default defineConfig({
    viewportWidth: 1280,
    viewportHeight: 720,
    defaultCommandTimeout: 20000,
    screenshotsFolder: 'screenshots',
    videosFolder: 'videos',
    trashAssetsBeforeRuns: true,
    env: {
        failSilently: false,
        SNAPSHOT_BASE_DIRECTORY: 'snapshots',
        SNAPSHOT_DIFF_DIRECTORY: 'snapshotDiffs',
        skipSnapshots: false,
    },
    e2e: {
        setupNodeEvents(on, config) {
            getCompareSnapshotsPlugin(on, config);

            const group = Cypress.env('GROUP') || 'default-group';

            if (group === 'default-group') {
                config.specPattern = ['e2e/**/*.cy.ts'];
            } else if (group === 'auth') {
                config.specPattern = 'e2e/authentication/*.cy.ts';
            } else if (group === 'cart-order-payment') {
                config.specPattern = ['e2e/cart/*.cy.ts', 'e2e/order/*.cy.ts', 'e2e/transportAndPayment/*.cy.ts'];
            } else if (group === 'visits') {
                config.specPattern = 'e2e/visits/*.cy.ts';
            }

            return config;
        },
        baseUrl: 'http://127.0.0.1:8000/',
        supportFile: 'support/index.ts',
    },
    retries: {
        runMode: 2,
    },
});
