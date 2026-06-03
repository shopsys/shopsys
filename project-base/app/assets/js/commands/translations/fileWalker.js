const { glob } = require('glob');

function fileWalker(dirs, done, ignoreMocks = true) {
    if (!Array.isArray(dirs)) {
        dirs = [dirs];
    }

    const promises = dirs.map(dir => {
        let ignore = {};

        if (!dir.match(/\/node_modules\//)) {
            ignore = { ignore: '**/node_modules/**' };
        }

        return glob(dir, ignore);
    });

    Promise.all(promises)
        .then(allFilepaths => {
            let concatedFilePaths = [];

            allFilepaths.forEach(filePaths => {
                concatedFilePaths = concatedFilePaths.concat(filePaths);
            });

            if (ignoreMocks) {
                concatedFilePaths = concatedFilePaths.filter(filePath => filePath.match(/\/mocks\//) === null);
            }
            done(null, concatedFilePaths);
        })
        .catch(done);
}

module.exports = fileWalker;
