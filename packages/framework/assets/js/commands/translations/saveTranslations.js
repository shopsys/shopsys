const fs = require('fs');
const PO = require('pofile');
const fileWalker = require('./fileWalker');
const findLang = require('./findLang');

function saveTranslations (translations, dirWithJsFiles, dirWithTranslations, outputDirForExportedTranslations) {

    fileWalker(dirWithTranslations, (err, filePaths) => {
        if (err) {
            console.log(err);
        }

        const promises = filePaths.map(filePath => {

            if (filePath.match(/(\w*)\.po$/) === null) {
                return;
            }

            const lang = findLang(filePath);
            return new Promise((resolve, reject) => {
                PO.load(filePath, (err, po) => {
                    if (err) {
                        console.log(err);
                    }

                    const translated = [];
                    po.items
                        .filter(item => translations.includes(item.msgid))
                        .forEach(item => {
                            translated.push({
                                msgid: item.msgid,
                                msgstr: item.msgstr[0]
                            });
                        });
                    resolve({ lang, translated });
                });
            });
        });

        Promise.all(promises).then(value => {
            const translations = {};
            value.forEach(translatedObject => {
                if (!translations[translatedObject.lang]) {
                    translations[translatedObject.lang] = [];
                }

                translations[translatedObject.lang] = translations[translatedObject.lang].concat(translatedObject.translated);
            });

            fs.writeFile(outputDirForExportedTranslations + 'translations.json', JSON.stringify(translations), (err) => {
                if (err) {
                    return console.log(err);
                }
            });
        });
    });
}

module.exports = saveTranslations;
