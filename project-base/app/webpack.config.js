const Encore = require('@symfony/webpack-encore');
const EventHooksPlugin = require('event-hooks-webpack-plugin');
const processTrans = require('./assets/js/commands/translations/process');
const CopyPlugin = require('copy-webpack-plugin');
const yaml = require('js-yaml');
const fs = require('fs');
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
    // hp entry?
    // order entry?
    // product entry?
    // cart entry?
    .addEntry('admin', './assets/js/admin/admin.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabel(null, {
        includeNodeModules: ['@shopsys'],
    })
    .enableBuildNotifications()
    .configureWatchOptions(function (watchOptions) {
        watchOptions.ignored = '**/*.json';
    })
    .addPlugin(new EventHooksPlugin({
        beforeRun: () => {
            const dirWithJsFiles = [
                sources.getFrameworkNodeModulesDir() + '/js/**/*.js',
                './assets/js/**/*.js'
            ];
            const dirWithTranslations = [
                sources.getFrameworkVendorDir() + '/src/Resources/translations/*.po',
                './translations/*.po',
            ];
            const outputDirForExportedTranslations = './assets/js/';

            try {
                processTrans(dirWithJsFiles, dirWithTranslations, outputDirForExportedTranslations);
            } catch (e) {
                console.log('Parsing files for translations has failed.');
            }
        }
    }))
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
                globOptions: {
                    ignore: ['assets/public/admin/svg/**/*']
                },
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
    .enableLessLoader()
    .enablePostCssLoader()
;

const config = Encore.getWebpackConfig();

config.resolve.alias = {
    'jquery-ui': 'jquery-ui/ui/widgets',
    'framework': '@shopsys/framework/js',
    'jquery': path.resolve(path.join(__dirname, 'node_modules', 'jquery')),
    'jquery-ui-styles': path.resolve(path.join(__dirname, 'node_modules', 'jquery-ui')),
    'bazinga-translator': path.resolve(path.join(__dirname, 'node_modules', 'bazinga-translator')),
    'jquery-ui-nested-sortable': path.resolve(path.join(__dirname, 'node_modules', 'nestedSortable'))
};
module.exports = config;
