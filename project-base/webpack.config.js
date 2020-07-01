const Encore = require('@symfony/webpack-encore');
const EventHooksPlugin = require('event-hooks-webpack-plugin');
const processTrans = require('./assets/js/commands/translations/process');
const generateWebFont = require('./assets/js/commands/svg/generateWebFont');
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
    .setPublicPath('/build')
    .setManifestKeyPrefix('web')
    .cleanupOutputBeforeBuild()
    .autoProvidejQuery()
    .addEntry('frontend', './assets/js/frontend.js')
    // hp entry?
    // order entry?
    // product entry?
    // cart entry?
    .addEntry('styleguide', './assets/js/styleguide/styleguide.js')
    .addEntry('admin', './assets/js/admin/admin.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabel(() => {}, {
        includeNodeModules: ['@shopsys'],
        useBuiltIns: 'usage',
        corejs: 3
    })
    .enableBuildNotifications()
    .configureWatchOptions(function (watchOptions) {
        watchOptions.ignored = '**/*.json';
    })
    .addPlugin(new EventHooksPlugin({
        beforeRun: () => {
            generateWebFont(
                'frontend',
                './assets/public/frontend/svg/'
            );
            generateWebFont(
                'admin',
                sources.getFrameworkNodeModulesDir() + '/public/admin/svg/',
                './web/public/admin/svg/'
            );

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
    .addPlugin(new CopyPlugin([
        { from: 'web/bundles/fpjsformvalidator', to: '../../assets/js/bundles/fpjsformvalidator', force: true },
        { from: 'node_modules/@shopsys/framework/public/admin', to: '../../web/public/admin', force: true },
        { from: 'assets/public', to: '../../web/public', ignore: ['assets/public/admin/svg/**/*'], force: true }
    ]))
    .addPlugin(new LiveReloadPlugin())
;

const domainFile = './config/domains.yaml';
const domains = yaml.safeLoad(fs.readFileSync(domainFile, 'utf8'));

const domainStylesDirectories = new Set(domains.domains.map(domain => {
    if (!domain.styles_directory) {
        return 'common';
    }

    return domain.styles_directory;
}));

domainStylesDirectories.forEach(stylesDirectory => {
    Encore
        .addEntry('frontend-style-' + stylesDirectory, './assets/styles/frontend/' + stylesDirectory + '/main.less')
        .addEntry('frontend-print-style-' + stylesDirectory, './assets/styles/frontend/' + stylesDirectory + '/print/main.less')
        .addEntry('frontend-wysiwyg-' + stylesDirectory, './assets/styles/frontend/' + stylesDirectory + '/wysiwyg.less');
});

Encore
    .addEntry('admin-style', './assets/styles/admin/main.less')
    .addEntry('admin-wysiwyg', './assets/styles/admin/wysiwyg.less')
    .addEntry('styleguide-style', './assets/styles/styleguide/main.less')
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
