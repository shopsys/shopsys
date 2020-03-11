const Encore = require('@symfony/webpack-encore');
const EventHooksPlugin = require('event-hooks-webpack-plugin');
const processTrans = require('./assets/js/commands/translations/process');
const CopyPlugin = require('copy-webpack-plugin');
const path = require('path');

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
        useBuiltIns: 'usage',
        corejs: 3
    })
    .enableBuildNotifications()
    .configureWatchOptions(function (watchOptions) {
        watchOptions.ignored = '**/*.json';
    })
    .addPlugin(new EventHooksPlugin({
        done: () => {
            const dirWithJsFiles = './assets/js/**/*';
            const dirWithTranslations = './translations/*.po';
            const outputDirForExportedTranslations = Encore.isProduction() ? './web/build/' : './assets/js/';

            try {
                processTrans(dirWithJsFiles, dirWithTranslations, outputDirForExportedTranslations);
            } catch (e) {
                console.log('Parsing files for translations has failed.');
            }
        }
    }))
    .addPlugin(new CopyPlugin([
        { from: 'web/bundles/fpjsformvalidator', to: '../../assets/js/bundles/fpjsformvalidator', force: true },
        { from: 'web/bundles/shopsysframework', to: '../../web/assets/admin/images', force: true }
    ]))
;


// Frontend Config
const WebfontsGenerator = require('webfonts-generator');
const StylelintPlugin = require('stylelint-webpack-plugin');
var requireContext = require('require-context');
yaml = require('js-yaml');
fs = require('fs');

SVGO = require('svgo'),
svgo = new SVGO();

const svgFilesFrontend = requireContext('../../src/Resources/svg/front', false, '.svg');
var svgFilesFrontendPath = [];
svgFilesFrontend.keys().forEach((filePathFrontend) => {
    var newFileFrontendPath = './src/Resources/svg/front/' + filePathFrontend;
    svgFilesFrontendPath.push(newFileFrontendPath);

    fs.readFile(newFileFrontendPath, 'utf8', function(err, data) {

        if (err) {
            throw err;
        }

        svgo.optimize(data, {path: newFileFrontendPath}).then(function(result) {

            //console.log(result);
            fs.writeFile(newFileFrontendPath, result.data, function(err){
                console.log("Frontend SVG icon " + newFileFrontendPath + " optimized");
            });

        });
    });
});

const svgFilesAdmin = requireContext('../../../packages/framework/src/Resources/svg/admin', false, '.svg');
var svgFilesAdminPath = [];
svgFilesAdmin.keys().forEach((filePathAdmin) => {
    var newFileAdminPath = '../packages/framework/src/Resources/svg/admin/' + filePathAdmin;
    svgFilesAdminPath.push(newFileAdminPath);

    fs.readFile(newFileAdminPath, 'utf8', function(err, data) {

        if (err) {
            throw err;
        }

        svgo.optimize(data, {path: newFileAdminPath}).then(function(result) {

            //console.log(result);
            fs.writeFile(newFileAdminPath, result.data, function(err){
                console.log("Admin SVG icon " + newFileAdminPath + " optimized");
            });

        });
    });
});

const domainFile = './config/domains.yml';
var domains = yaml.safeLoad(fs.readFileSync(domainFile, 'utf8'));

domains.domains.forEach((domain) => {
    if(!domain.styles_directory){
        domain.styles_directory = 'common';
    }
    Encore
        .addEntry('frontend-style-'+domain.styles_directory, './src/Resources/styles/front/'+domain.styles_directory+'/main.less')
        .addEntry('frontend-print-style-'+domain.styles_directory, './src/Resources/styles/front/'+domain.styles_directory+'/print/main.less')
    ;
    }
)

Encore
    .addEntry('admin-style', '../packages/framework/src/Resources/styles/admin/main.less')
    .addPlugin (
        new StylelintPlugin({
            configFile: '.stylelintrc',
            files: 'src/**/*.less'
        })
    )
    .addLoader(
        new WebfontsGenerator({
            files: svgFilesFrontendPath,
            dest: 'web/assets/frontend/fonts',
            cssDest: 'src/Resources/styles/front/common/libs/svg.less',
            cssFontsUrl: '/assets/frontend/fonts',
            fontName: 'svg',
            fontHeight: '512',
            fixedWidth: '512',
            centerHorizontally: true,
            normalize: true,
            html: true,
            htmlDest: 'docs/generated/webfont-frontend-svg.html',
            templateOptions: {
                baseSelector: '.svg',
                classPrefix: 'svg-'
            }
        }, function(error) {
            if (error) {
                console.log('Frontend SVG Fail!', error);
            } else {
                console.log('Frontend SVG generated!');
            }
        })
    )
    .addLoader(
        new WebfontsGenerator({
            files: svgFilesAdminPath,
            dest: 'web/assets/admin/fonts',
            cssDest: '../packages/framework/src/Resources/styles/admin/libs/svg.less',
            cssFontsUrl: '/assets/admin/fonts',
            fontName: 'svg',
            fontHeight: '512',
            fixedWidth: '512',
            centerHorizontally: true,
            normalize: true,
            html: true,
            htmlDest: 'docs/generated/webfont-admin-svg.html',
            templateOptions: {
                baseSelector: '.svg',
                classPrefix: 'svg-'
            }
        }, function(error) {
            if (error) {
                console.log('Admin SVG Fail!', error);
            } else {
                console.log('Admin SVG generated!');
            }
        })
    )
    .addLoader({
        test   : /\.(ttf|eot|svg|woff(2)?)(\?[a-z0-9=&.]+)?$/,
        loader : 'file-loader'
    })
    .enableLessLoader()
    .enablePostCssLoader()
;

const config = Encore.getWebpackConfig();

config.resolve.alias = {
    'jquery-ui': 'jquery-ui/ui/widgets/',
    'framework': '@shopsys/framework/js',
    'jquery': path.resolve(path.join(__dirname, 'node_modules', 'jquery'))
};
module.exports = config;
