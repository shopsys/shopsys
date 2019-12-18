const Encore = require('@symfony/webpack-encore');
const EventHooksPlugin = require('event-hooks-webpack-plugin');
const processTrans = require('../packages/framework/assets/js/commands/translations/process');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('web/build/')
    .setPublicPath('/build')
    .setManifestKeyPrefix('web')
    .cleanupOutputBeforeBuild()
    .addEntry('app', './assets/js/app.js')
    // hp entry?
    // order entry?
    // product entry?
    // cart entry?
    .addEntry('styleguide', './assets/js/styleguide/styleguide.js')
    .addEntry('admin', '../packages/framework/assets/js/admin/index.js')
    .addEntry('jquery', '../packages/framework/assets/js/admin/jquery.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabel(() => {}, {
        useBuiltIns: 'usage',
        corejs: 3
    })
    .enableBuildNotifications()
    .configureWatchOptions(function(watchOptions) {
        watchOptions.ignored = '**/*.json';
    })
    .addPlugin(new EventHooksPlugin({
        done: () => {
            const dirWithJsFiles = './assets/js/';
            const dirWithTranslations = './translations/';
            const outputDirForExportedTranslations = Encore.isProduction() ? './web/build/' : './assets/js/';

            processTrans(dirWithJsFiles, dirWithTranslations, outputDirForExportedTranslations);
        }
    }))
;

const config = Encore.getWebpackConfig();

config.resolve.alias = {
    'jquery-ui': 'jquery-ui/ui/widgets/'
};

module.exports = config;
