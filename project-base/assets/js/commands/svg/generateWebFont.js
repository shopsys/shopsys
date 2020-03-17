const WebfontsGenerator = require('webfonts-generator');

function generateWebFont (type, svgFilesPath) {
    WebfontsGenerator({
        files: svgFilesPath,
        dest: 'assets/public/' + type + '/fonts',
        cssDest: 'assets/styles/' + type + '/common/libs/svg.less',
        cssFontsUrl: '/assets/public/' + type + '/fonts',
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
}
module.exports = generateWebFont;
