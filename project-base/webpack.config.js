var Encore = require('@symfony/webpack-encore');

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
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabel(() => {}, {
        useBuiltIns: 'usage',
        corejs: 3
    })
    .enableBuildNotifications()
;

module.exports = Encore.getWebpackConfig();
