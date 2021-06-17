const WebfontsGenerator = require('@vusion/webfonts-generator');
const optimizeSvg = require('./optimizeSvg');

function generateWebFont (type, svgSourceFolder, svgDestinationFolder = null) {
    optimizeSvg(svgSourceFolder, svgDestinationFolder).then(svgFilesPath => {
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
module.exports = generateWebFont;
