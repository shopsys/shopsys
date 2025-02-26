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
    .enableBuildNotifications()
    .configureWatchOptions(function (watchOptions) {
        watchOptions.ignored = '**/*.json';
    })
    .addPlugin(new CopyPlugin({
        patterns: [
            {
                from: 'web/bundles/fpjsformvalidator',
                to: '../../assets/js/bundles/fpjsformvalidator',
                force: true
            },
            {
                from: sources.getFrameworkNodeModulesDir() + '/public/admin',
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
    .addEntry('admin-style', './assets/styles/admin/main.less')
    .addEntry('admin-wysiwyg', './assets/styles/admin/wysiwyg.less')
    .addPlugin(
        new StylelintPlugin({
            configFile: '.stylelintrc',
            files: 'assets/styles/**/*.less'
        })
    )
    .enableLessLoader(function (options) {
        options.lessOptions = {
            math: 'always'
        };
    })
    .enablePostCssLoader();

Encore.addAliases({
    'jquery-ui': 'jquery-ui/ui/widgets',
    'framework': '@shopsys/framework/js',
    'jquery': path.resolve(path.join(sources.getNodeModulesDir(), 'jquery')),
    'jquery-ui-styles': path.resolve(path.join(sources.getNodeModulesDir(), 'jquery-ui')),
    'bazinga-translator': path.resolve(path.join(sources.getNodeModulesDir(), 'bazinga-translator')),
    'jquery-ui-nested-sortable': path.resolve(path.join(sources.getNodeModulesDir(), 'nestedSortable')),
    'icons': path.resolve(path.join(__dirname, 'assets/icons'))
});

module.exports = Encore.getWebpackConfig();
