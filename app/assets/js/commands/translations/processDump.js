const fs = require('fs');
const parseFile = require('./parseFile');
const fileWalker = require('./fileWalker');

function processDump (dirWithJsFiles, outputDirForExportedTranslations) {
    fileWalker(dirWithJsFiles, (err, filePaths) => {
        if (err) {
            console.log(err);
        }

        const translations = [];

        filePaths.map(filePath => {
            if (filePath.match(/(\w*)\.js$/) === null) {
                return;
            }

            parseFile(filePath).forEach(translation => {
                if (!translations.includes(translation)) {
                    translations.push(translation);
                }
            });
        });

        fs.writeFile(outputDirForExportedTranslations + 'translationsDump.json', JSON.stringify(translations), (writeErr) => {
            if (writeErr) {
                return console.log(writeErr);
            }
            return console.log('Translation dump was save in ' + outputDirForExportedTranslations + 'translationsDump.json');
        });
    });
}

module.exports = processDump;
