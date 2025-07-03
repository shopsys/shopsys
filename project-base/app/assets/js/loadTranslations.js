import Translation from 'bazinga-translator';
import Register from 'framework/common/utils/Register';

const translations = require('./translations.json');

export default function loadTranslations() {
    Object.keys(translations).forEach(locale => {
        translations[locale].forEach(translation => {
            let msgstr = translation.msgstr;
            if (msgstr === '') {
                msgstr = translation.msgid;
            }
            Translation.add(translation.msgid, msgstr, null, locale);
        });
    });
}

new Register().registerCallback(loadTranslations, 200);
