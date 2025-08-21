import { defineConfig } from 'cypress';
import { configureVisualRegression } from 'cypress-visual-regression';
import { globSync } from 'glob';

const getProjectPages = () => {
    const files = globSync('**/*.tsx', {
        cwd: './pages',
        ignore: ['\\[...all].tsx', '_*.tsx', 'robots.txt.tsx'],
    }).reverse();

    /*
        Transform filepaths to routes:
            1. cart.tsx => /cart
            2. index.tsx => /
            3. personal-data-overview/index.tsx => /personal-data-overview
            3. personal-data-overview/[hash].tsx => /personal-data-overview/:hash
     */
    return files.map((path) => {
        const replacedPath = path
            .replace('.tsx', '')
            .replace('/index', '')
            .replace('index', '')
            .replace(/\[(.*[^\]])]/, ':$1');

        return `/${replacedPath}`;
    });
};

export default defineConfig({
    viewportWidth: 1280,
    viewportHeight: 720,
    defaultCommandTimeout: 20000,
    screenshotsFolder: 'screenshots',
    video: true,
    videosFolder: 'videos',
    trashAssetsBeforeRuns: true,
    env: {
        skipSnapshots: false,
        visualRegressionErrorThreshold: 0.005,
        visualRegressionFailSilently: false,
        visualRegressionBaseDirectory: 'snapshots',
        visualRegressionDiffDirectory: 'snapshotDiffs',
    },
    e2e: {
        setupNodeEvents(on, config) {
            configureVisualRegression(on);

            const { globSync } = require('glob');
            const group = process.env.GROUP || 'default-group';
            const isSmoke = process.env.COMMAND === 'smoke';

            if (isSmoke) {
                config.specPattern = ['smokeTests/smokeTests.cy.ts'];
            } else {
                const patternsMap: { [key: string]: string[] } = {
                    authentication: ['e2e/authentication/*.cy.ts'],
                    cart: ['e2e/cart/*.cy.ts'],
                    order: ['e2e/order/*.cy.ts'],
                    transportAndPayment: ['e2e/transportAndPayment/*.cy.ts'],
                    visits: ['e2e/visits/*.cy.ts'],
                };

                const usedPatterns = Object.values(patternsMap).flat();

                if (group === 'default-group') {
                    config.specPattern = ['e2e/**/*.cy.ts'];
                } else if (group in patternsMap) {
                    config.specPattern = patternsMap[group];
                } else if (group === 'others') {
                    const allFiles = globSync('e2e/**/*.cy.ts').reverse();

                    const filteredFiles = allFiles.filter(
                        (file: string) =>
                            !usedPatterns.some((pattern) => new RegExp(pattern.replace('*', '.*')).test(file)),
                    );

                    config.specPattern = filteredFiles.length > 0 ? filteredFiles : ['e2e/matrix/matrixTest.cy.ts'];
                }
            }

            config.env.projectPages = getProjectPages();

            return config;
        },
        baseUrl: 'http://127.0.0.1:8000/',
        specPattern: 'e2e/**/*.cy.ts',
        supportFile: 'support/index.ts',
    },
    retries: {
        runMode: 2,
    },
});
