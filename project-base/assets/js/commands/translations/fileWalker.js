const glob = require('glob');

function fileWalker (dirs, done) {
    if (!Array.isArray(dirs)) {
        dirs = [dirs];
    }

    const promises = dirs.map(dir => new Promise((resolve, reject) => {
        glob(dir, { ignore: '**/node_modules/**' }, (err, filePaths) => {
            if (err) {
                reject(err);
            }

            resolve(filePaths);
        });
    }));

    Promise.all(promises).then(allFilepaths => {
        let concatedFilePaths = [];

        allFilepaths.forEach(filePaths => {
            concatedFilePaths = concatedFilePaths.concat(filePaths);
        });

        done(null, concatedFilePaths);
    });
}

module.exports = fileWalker;
