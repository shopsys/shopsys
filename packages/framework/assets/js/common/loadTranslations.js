import Register from './register';
import Translation from 'bazinga-translator';
const translations = require('../translations.json');

export default function loadTranslations () {
    Object.keys(translations).forEach(locale => {
        translations[locale].forEach(translation => {
            Translation.add(translation.msgid, translation.msgstr, null, locale);
        });
    });
}

(new Register()).registerCallback(loadTranslations);
