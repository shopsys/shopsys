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

            const patternsMap: { [key: string]: string[] } = {
                authentication: ['e2e/authentication/*.cy.ts'],
                cartOrderTransportAndPayment: [
                    'e2e/cart/*.cy.ts',
                    'e2e/order/*.cy.ts',
                    'e2e/transportAndPayment/*.cy.ts',
                ],
                visits: ['e2e/visits/*.cy.ts'],
            };

            const usedPatterns = Object.values(patternsMap).flat();

            if (group === 'default-group') {
                config.specPattern = ['e2e/**/*.cy.ts'];
            } else if (group in patternsMap) {
                config.specPattern = patternsMap[group];
            } else if (group === 'others') {
                const allFiles = glob.sync('e2e/**/*.cy.ts');

                const filteredFiles = allFiles.filter(
                    (file: string) =>
                        !usedPatterns.some((pattern) => new RegExp(pattern.replace('*', '.*')).test(file)),
                );

                config.specPattern = filteredFiles.length > 0 ? filteredFiles : ['e2e/dummy/dummyTest.cy.ts'];
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
