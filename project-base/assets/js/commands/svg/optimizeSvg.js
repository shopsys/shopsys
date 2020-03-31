const glob = require('glob');
const fs = require('fs');
const SVGO = require('svgo');

const svgo = new SVGO();

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
                        fs.writeFile(svgFile, result.data, function (errorOptimize) {
                            if (errorOptimize) {
                                console.log('ERROR: SVG icon ' + svgFile + ' optimize failed');
                                throw errorOptimize;
                            }
                        });
                    });
                });
            });

            resolve(svgFiles);
        });
    });
}

module.exports = optimizeSvg;
