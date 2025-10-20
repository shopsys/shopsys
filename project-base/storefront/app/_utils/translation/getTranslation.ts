import { getDomainConfig } from 'app/_utils/getDomainConfig';
import { createTranslation } from 'app/_utils/translation/translation';
import { Locale } from 'i18n-config';
import 'server-only';
import { Dictionary, Translate } from 'types/translation';
import { getDictionary } from 'utils/getDictionary';

export const getTranslation = async (props?: {
    defaultDictionary?: Dictionary;
    defaultLang?: Locale;
}): Promise<Translate> => {
    let lang = props?.defaultLang;
    let dictionary = props?.defaultDictionary;

    if (!lang) {
        const domainConfig = await getDomainConfig();
        lang = domainConfig.defaultLocale;
    }

    if (!dictionary) {
        dictionary = await getDictionary(lang);
    }

    const { t } = createTranslation(dictionary, new Intl.PluralRules(lang));

    return t;
};
