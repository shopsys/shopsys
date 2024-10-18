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

            const glob = require('glob');
            const group = process.env.GROUP || 'default-group';

            const assignedPatterns: string[] = [];

            if (group === 'default-group') {
                config.specPattern = ['e2e/**/*.cy.ts'];
            } else if (group === 'authentication') {
                assignedPatterns.push('e2e/authentication/*.cy.ts');
                config.specPattern = assignedPatterns;
            } else if (group === 'cart-order-transportAndPayment') {
                assignedPatterns.push('e2e/cart/*.cy.ts', 'e2e/order/*.cy.ts', 'e2e/transportAndPayment/*.cy.ts');
                config.specPattern = assignedPatterns;
            } else if (group === 'visits') {
                assignedPatterns.push('e2e/visits/*.cy.ts');
                config.specPattern = assignedPatterns;
            } else if (group === 'others') {
                const allFiles = glob.sync('e2e/**/*.cy.ts');

                config.specPattern = allFiles.filter(
                    (file: string) =>
                        !assignedPatterns.some((pattern) => new RegExp(pattern.replace('*', '.*')).test(file)),
                );
            }

            if (group !== 'others' && group !== 'default-group') {
                config.specPattern = assignedPatterns;
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
