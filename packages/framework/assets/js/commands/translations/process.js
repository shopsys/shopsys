const parseFile = require('./parseFile');
const fileWalker = require('./fileWalker');
const saveTranslations = require('./saveTranslations');

function process (dirWithJsFiles, dirWithTranslations, outputDirForExportedTranslations) {
    fileWalker(dirWithJsFiles,(err, filePaths) => {
        const translations = [];

        filePaths.map(filePath => {
            if(filePath.match(/(\w*)\.js$/) === null){
                return;
            }

            parseFile(filePath).forEach(translation => {
                if (!translations.includes(translation)) {
                    translations.push(translation);
                }
            });
        });

        saveTranslations(translations, dirWithJsFiles, dirWithTranslations, outputDirForExportedTranslations);
    });
}

module.exports = process;
