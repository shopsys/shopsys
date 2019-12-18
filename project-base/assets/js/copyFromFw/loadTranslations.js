import Register from './register';
const translations = require('../translations.json');
import Translation from 'bazinga-translator';

export default function loadTranslations () {
    Object.keys(translations).forEach(locale => {
        translations[locale].forEach(translation => {
            Translation.add(translation.msgid, translation.msgstr, null, locale);
        });
    });
}

(new Register()).registerCallback(loadTranslations);
