const { defineConfig } = require('cypress');

module.exports = defineConfig({
    experimentalStudio: true,
    viewportWidth: 1920,
    viewportHeight: 1080,
    defaultCommandTimeout: 20000,
    fixturesFolder: 'fixtures',
    screenshotsFolder: 'screenshots',
    videosFolder: 'videos',
    e2e: {
        // We've imported your old cypress plugins here.
        // You may want to clean this up later by importing these.
        setupNodeEvents(on, config) {
            return require('./plugins/index.js')(on, config);
        },
        baseUrl: 'http://127.0.0.1:8000/',
        specPattern: 'integration/Tests/**/*.cy.{js,jsx,ts,tsx}',
        supportFile: 'support/index.js',
    },
});
