const parseFile = require('./parseFile');
const fileWalker = require('./fileWalker');
const findAndSaveTranslations = require('./findAndSaveTranslations');

function process (dirWithJsFiles, dirWithTranslations, outputDirForExportedTranslations) {
    fileWalker(dirWithJsFiles, (err, filePaths) => {
        if (err) {
            console.log(err);
        }

        const translations = [];

        filePaths.map(filePath => {
            parseFile(filePath).forEach(translation => {
                if (!translations.includes(translation)) {
                    translations.push(translation.id);
                }
            });
        });

        findAndSaveTranslations(translations, dirWithJsFiles, dirWithTranslations, outputDirForExportedTranslations);
    });
}

module.exports = process;
