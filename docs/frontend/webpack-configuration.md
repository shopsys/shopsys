# Webpack configuration
Webpack configuration for CSS, Images, Svg font is devided by JS Comment `// Frontend Config`

## Config file content description

`const WebfontsGenerator = require('webfonts-generator');` - plugin for generating SVG font from SVG files

`const StylelintPlugin = require('stylelint-webpack-plugin');` - plugin for coding standards in less files (see also [StyleLint Settings](./stylelint-settings.md))

`var requireContext = require('require-context');` - plugin for loading array of files in specified folder

`yaml = require('js-yaml');` - plugin for loading yaml files to usable array

`fs = require('fs');` - plugin for file system functions (saving files)

`SVGO = require('svgo'),` - plugin for svg optimalization svg files
`svgo = new SVGO();` - svgo init

```
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
```
This part loads all svg files from `../../src/Resources/svg/front` and aplies `svg.optimize` function and saves new file. Similar code is used admin svg files.

```
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
```
We can specify different design for each domain. This is specified by `styles_directory` value in `./config/domains.yml` file. By default we have `common` and `domain2`. For all these folder we need to create `.addEntry` for style and for print style. These entries are loaded in `base.html.twig` by:
```
{% set entryDirectory = 'frontend-style-' ~ getDomain().currentDomainConfig.stylesDirectory %}
{{ encore_entry_link_tags( entryDirectory ) }}
```

## Encore configuration description

```
Encore
    .addEntry('admin-style', '../packages/framework/src/Resources/styles/admin/main.less')
    .addPlugin (
        new StylelintPlugin({
            configFile: '.stylelintrc',
            files: 'src/**/*.less'
        })
    )
```
This part creates entry for admin file - generates admin css file from specified `main.less` and aplies stylelint rules for all frontend less files.

```
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
```
This part configures generating SVG font from svg files. There is used array `svgFilesFrontendPath` from previous step to apply this plugin for all svg files. For admin files there is used similar `.addLoader`.

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
