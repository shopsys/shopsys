const requireContext = require('require-context');
const fs = require('fs');

SVGO = require('svgo');
const svgo = new SVGO();

function optimizeSvg (type) {
    if (type == 'frontend') {
        var svgSourceFolder = '../../assets/public/frontend/svg';
        var svgTargetFolder = 'assets/public/frontend/svg';
    }
    if (type == 'admin') {
        var svgSourceFolder = '../../node_modules/@shopsys/framework/assets/public/svg/admin';
        var svgTargetFolder = 'assets/public/admin/svg';
    }

    var svgFiles = requireContext(svgSourceFolder, false, '.svg');
    const svgFilesPath = [];
    svgFiles.keys().forEach((filePath) => {
        const newFilePath = svgTargetFolder + '/' + filePath;
        svgFilesPath.push(newFilePath);

        fs.readFile(newFilePath, 'utf8', function (err, data) {
            if (err) {
                throw err;
            }
            svgo.optimize(data, { path: newFilePath }).then(function (result) {
                fs.writeFile(newFilePath, result.data, function (err) {
                    if (err) {
                        throw err;
                    }
                    console.log(type + ' SVG icon ' + newFilePath + ' optimized');
                });
            });
        });
    });
    return svgFilesPath;
}

module.exports = optimizeSvg;
