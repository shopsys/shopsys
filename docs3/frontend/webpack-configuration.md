# Webpack configuration

Webpack configuration for CSS, Images, Svg font is devided by JS Comment `// Frontend Config`

## Config file content description

`const generateWebFont = require('./assets/js/commands/svg/generateWebFont');` - plugin for generating SVG font from optimized SVG files

`yaml = require('js-yaml');` - plugin for loading yaml files to usable array

`fs = require('fs');` - plugin for file system functions (saving files)

`const StylelintPlugin = require('stylelint-webpack-plugin');` - plugin for coding standards in less files (see also [StyleLint Settings](./stylelint-settings.md))

```
const domainFile = './config/domains.yaml';
const domains = yaml.safeLoad(fs.readFileSync(domainFile, 'utf8'));

domains.domains.forEach((domain) => {
    if (!domain.styles_directory) {
        domain.styles_directory = 'common';
    }
    Encore
        .addEntry('frontend-style-' + domain.styles_directory, './assets/styles/frontend/' + domain.styles_directory + '/main.less')
        .addEntry('frontend-print-style-' + domain.styles_directory, './assets/styles/frontend/' + domain.styles_directory + '/print/main.less');
});
```

We can specify different design for each domain. This is specified by `styles_directory` value in `./config/domains.yaml` file. By default we have `common` and `domain2`. For all these folders we need to create `.addEntry` for style and for print style. These entries are loaded in `base.html.twig` by:

```
{% set entryDirectory = 'frontend-style-' ~ getDomain().currentDomainConfig.stylesDirectory %}
{{ encore_entry_link_tags( entryDirectory ) }}
```

## Encore configuration description

```
    .addPlugin(new EventHooksPlugin({
        beforeRun: () => {
            generateWebFont(
                'frontend',
                './assets/public/frontend/svg/*.svg'
            );
            generateWebFont(
                'admin',
                './assets/public/admin/svg/*.svg'
            );
        },
```

This part generates svg webfonts for admin and frontend. It uses local function `generateWebFont = require('./assets/js/commands/svg/generateWebFont')`. Lets look inside this function.

```
function generateWebFont (type, svgSourceFolder) {

    optimizeSvg(svgSourceFolder).then(svgFilesPath => {
        WebfontsGenerator({
            files: svgFilesPath,
            dest: 'assets/public/' + type + '/fonts',
            cssDest: 'assets/public/' + type + '_svg.less',
            cssFontsUrl: type + '/fonts',
            fontName: 'svg',
            fontHeight: '512',
            fixedWidth: '512',
            centerHorizontally: true,
            normalize: true,
            html: true,
            htmlDest: 'docs/generated/webfont-' + type + '-svg.html',
            templateOptions: {
                baseSelector: '.svg',
                classPrefix: 'svg-'
            }
        }, function (error) {
            if (error) {
                console.log(type + ' SVG Fail!', error);
            } else {
                console.log(type + ' SVG generated!');
            }
        });
    });
}
```

This function configures generating SVG font from optimized svg files.
Files in this folders are optimized by `optimizeSvg` functions which returns array of files. This array is sent to WebfontsGenerator as `svgFilesPath` to apply this plugin for all svg files.

```
function optimizeSvg (svgSourceFolder) {
    return new Promise((resolve, reject) => {
        return glob(svgSourceFolder, null, (err, svgFiles) => {
            if (err) {
                reject(err);
            }

            svgFiles.forEach(svgFile => {
                fs.readFile(svgFile, 'utf8', function (err, data) {
                    if (err) {
                        throw err;
                    }
                    svgo.optimize(data, { path: svgFile }).then(function (result) {
                        fs.writeFile(svgFile, result.data, function (err) {
                            if (err) {
                                console.log('ERROR: SVG icon ' + svgFile + ' optimize failed');
                                throw err;
                            }
                        });
                    });
                });
            });

            resolve(svgFiles);
        });
    });
}
```

This part loads all svg files from `svgSourceFolder` and aplies `svgo.optimize` function and saves new file.

```
Encore
    .addEntry('admin-style', './assets/styles/admin/main.less')
    .addPlugin(
        new StylelintPlugin({
            configFile: '.stylelintrc',
            files: 'assets/styles/**/*.less'
        })
    )
```

This part creates entry for admin file - generates admin css file from specified `main.less` and aplies stylelint rules for all frontend less files.

```
    .addLoader({
        test   : /\.(ttf|eot|svg|woff(2)?)(\?[a-z0-9=&.]+)?$/,
        loader : 'file-loader'
    })
    .enableLessLoader()
    .enablePostCssLoader()
    ;
```

This part adds loader for font files - it is necessary for font files. `.enableLessLoader()` init for convert less files to css files.

`.enablePostCssLoader()` - this will apply all rules from `postcss.config.js` file in root of project-base. By default there is set autoprefixer for `last 3 versions` browsers.
