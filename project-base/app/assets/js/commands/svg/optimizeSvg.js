const fs = require('fs');
const glob = require('glob');
const path = require('path');
const SVGO = require('svgo');

const writeSvg = (svgOptimizedFile, optimizedSvg) => {
    try {
        fs.writeFileSync(svgOptimizedFile, optimizedSvg);
    } catch (err) {
        console.log('ERROR: SVG icon ' + svgOptimizedFile + ' optimize failed');
    }
};

function optimizeSvg (svgSourceFolder, svgDestinationFolder = null) {
    const svgo = new SVGO();

    if (svgDestinationFolder === null) {
        svgDestinationFolder = svgSourceFolder;
    }

    if (!fs.existsSync(svgDestinationFolder)) {
        fs.mkdirSync(svgDestinationFolder, { recursive: true });
    }

    return new Promise((resolve, reject) => {
        glob(svgSourceFolder + '**/*.svg', null, (err, svgFiles) => {
            if (err) {
                reject(err);
            }

            const svgOptimizedFiles = svgFiles.map(svgFile => {
                const svgOptimizedFile = svgDestinationFolder + path.basename(svgFile);
                const optimizedSvg = fs.readFileSync(svgFile, 'utf8');
                svgo.optimize(optimizedSvg, { path: svgFile }).then(result => writeSvg(svgOptimizedFile, result.data));

                return svgOptimizedFile;
            });

            resolve(svgOptimizedFiles);
        });
    });
}

module.exports = optimizeSvg;
