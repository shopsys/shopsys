import { defineConfig } from 'cypress';
import { configureVisualRegression } from 'cypress-visual-regression';
import * as fs from 'fs';
import { globSync } from 'glob';
import * as path from 'path';

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
        TEST_LOCALE: process.env.TEST_LOCALE,
        B2B_DOMAIN_ID: process.env.B2B_DOMAIN_ID || '',
        B2B_BASE_URL: process.env.B2B_BASE_URL || '',
        skipSnapshots: false,
        visualRegressionErrorThreshold: 0.005,
        visualRegressionFailSilently: false,
        visualRegressionBaseDirectory: 'snapshots',
        visualRegressionDiffDirectory: 'snapshotDiffs',
    },
    e2e: {
        setupNodeEvents(on, config) {
            configureVisualRegression(on);

            on('before:browser:launch', (browser, launchOptions) => {
                if (browser.family === 'chromium' && browser.name !== 'electron') {
                    launchOptions.args.push(
                        '--font-render-hinting=none',
                        '--disable-font-subpixel-positioning',
                        '--disable-lcd-text',
                        '--disable-skia-runtime-opts',
                    );
                }

                return launchOptions;
            });

            // Dynamically get available dataFixtures.locale.po file for translations
            on('task', {
                getAvailablePoLocales() {
                    // Standard Docker mount path where translations are mounted
                    const candidatePaths = ['/app/app-translations'];

                    try {
                        const translationsDir = candidatePaths.find((p) => {
                            try {
                                return fs.existsSync(p) && fs.statSync(p).isDirectory();
                            } catch {
                                return false;
                            }
                        });

                        if (!translationsDir) {
                            console.warn('🟡 Translations directory not found in any of candidate paths.');
                            return ['en'];
                        }

                        const files = fs.readdirSync(translationsDir);
                        const poFiles = files.filter(
                            (file) => file.startsWith('dataFixtures.') && file.endsWith('.po'),
                        );

                        // Extract locales from filenames (e.g., "dataFixtures.cs.po" -> "cs")
                        const locales = poFiles
                            .map((file) => {
                                const match = file.match(/^dataFixtures\.([a-z]{2})\.po$/);
                                return match ? match[1] : null;
                            })
                            .filter(Boolean) as string[];

                        return locales.length > 0 ? locales : ['en']; // Fallback to English if no files found
                    } catch (error) {
                        console.error('❌ Error reading translations directory:', error);
                        return ['en'];
                    }
                },
            });

            const { globSync } = require('glob');
            const group = process.env.GROUP || 'default-group';
            const isSmoke = process.env.COMMAND === 'smoke';

            if (isSmoke) {
                config.specPattern = ['smokeTests/smokeTests.cy.ts'];
            } else {
                const patternsMap: { [key: string]: string[] } = {
                    authentication: ['e2e/authentication/*.cy.ts'],
                    b2b: ['e2e/b2b/*.cy.ts'],
                    cart: ['e2e/cart/*.cy.ts'],
                    comparison: ['e2e/comparison/*.cy.ts'],
                    complaints: ['e2e/complaints/*.cy.ts'],
                    customerUsers: ['e2e/customerUsers/*.cy.ts'],
                    filterAndSort: ['e2e/filterAndSort/*.cy.ts'],
                    freeShipping: ['e2e/freeShipping/*.cy.ts'],
                    limitedUser: ['e2e/limitedUser/*.cy.ts'],
                    order: ['e2e/order/*.cy.ts'],
                    seoCategory: ['e2e/seoCategory/*.cy.ts'],
                    ssr: ['e2e/ssr/*.cy.ts'],
                    stores: ['e2e/stores/*.cy.ts'],
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
