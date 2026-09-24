import Translation from 'bazinga-translator';

const translations = require('./translations.json');

/**
 * The catalogue is bundled with the admin JS, so it is added right when the bundle is evaluated – before
 * DOMContentLoaded, and therefore before Stimulus controllers connect or Register callbacks run
 */
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

loadTranslations();
