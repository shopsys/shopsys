const fs = require('fs');
const path = require('path');

function fileWalker (dir, done) {
    let results = [];

    fs.readdir(dir, function (readErr, list) {
        if (readErr) return done(readErr);

        let pending = list.length;

        if (!pending) return done(null, results);

        list.forEach(filePath => {
            filePath = path.resolve(dir, filePath);

            fs.stat(filePath, (statErr, stat) => {
                if (statErr) {
                    console.log(statErr);
                }

                // If directory, execute a recursive call
                if (stat && stat.isDirectory()) {
                    fileWalker(filePath, (walkErr, res) => {
                        if (walkErr) {
                            console.log(walkErr);
                        }

                        results = results.concat(res);
                        if (!--pending) done(null, results);
                    });
                } else {
                    results.push(filePath);
                    if (!--pending) done(null, results);
                }
            });
        });
    });
}

module.exports = fileWalker;
