const fs = require('fs');
const path = require('path');

function fileWalker (dir, done) {
    let results = [];

    fs.readdir(dir, function (err, list) {
        if (err) return done(err);

        let pending = list.length;

        if (!pending) return done(null, results);

        list.forEach(filePath => {
            filePath = path.resolve(dir, filePath);

            fs.stat(filePath, (err, stat) => {
                if (err) {
                    console.log(err);
                }

                // If directory, execute a recursive call
                if (stat && stat.isDirectory()) {
                    fileWalker(filePath, (err, res) => {
                        if (err) {
                            console.log(err);
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
