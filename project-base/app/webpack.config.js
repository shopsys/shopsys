const Encore = require('@symfony/webpack-encore');
const CopyPlugin = require('copy-webpack-plugin');
const path = require('path');
const StylelintPlugin = require('stylelint-webpack-plugin');
const sources = require('./assets/js/bin/helpers/sources');
const LiveReloadPlugin = require('webpack-livereload-plugin');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('web/build/')
    .setPublicPath((process.env.CDN_DOMAIN ? process.env.CDN_DOMAIN : '') + '/build')
    .setManifestKeyPrefix('web')
    .cleanupOutputBeforeBuild()
    .autoProvidejQuery()
    .addEntry('admin', './assets/js/admin/admin.js')
    .addEntry('administration', sources.getPackageNodeModulesDir('administration') + '/src/js/index.js')
    .enableStimulusBridge('./assets/controllers.json')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabel(null, {
        includeNodeModules: ['@shopsys']
    })
    .addRule({
        test: /\.svg/,
        type: 'asset/source'
    })
    .configureWatchOptions(function (watchOptions) {
        watchOptions.ignored = '**/*.json';
    })
    .addPlugin(new CopyPlugin({
        patterns: [
            {
                from: sources.getPackageNodeModulesDir('framework') + '/public/admin',
                to: '../../web/public/admin',
                force: true
            },
            {
                from: 'assets/public',
                to: '../../web/public',
                force: true
            },
            {
                from: 'assets/extra',
                to: '../../web',
                force: true
            }
        ]
    }))
    .addPlugin(new LiveReloadPlugin())
;

Encore
    .addEntry('admin-style', './assets/styles/admin/main.scss')
    .addPlugin(
        new StylelintPlugin({
            configFile: '.stylelintrc',
            files: 'assets/styles/**/*.scss',
        })
    )
    .enableSassLoader(options => {
        options.sassOptions = {
            quietDeps: true, // Silence deprecation warnings from dependencies
            silenceDeprecations: ['import'],
        };
    })
    .enablePostCssLoader()
;

Encore.addAliases({
    'framework': '@shopsys/framework/js',
    'jquery': path.resolve(path.join(sources.getNodeModulesDir(), 'jquery')),
    'bazinga-translator': path.resolve(path.join(sources.getNodeModulesDir(), 'bazinga-translator')),
    'icons': path.resolve(path.join(__dirname, 'assets/icons'))
});

module.exports = Encore.getWebpackConfig();
