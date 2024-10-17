import { defineConfig } from 'cypress';
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
        },
        baseUrl: 'http://127.0.0.1:8000/',
        specPattern: [
            'e2e/authentication/*.cy.ts',
            'e2e/cart/*.cy.ts',
            'e2e/order/*.cy.ts',
            'e2e/transportAndPayment/*.cy.ts',
            'e2e/visits/*.cy.ts',
        ],
        supportFile: 'support/index.ts',
    },
    retries: {
        runMode: 2,
    },
});
