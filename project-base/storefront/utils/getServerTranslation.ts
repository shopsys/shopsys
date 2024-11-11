import { getDomainConfig } from './domain/domainConfig';
import { createTranslation } from './translation';
import { Locale } from 'i18n-config';
import { headers } from 'next/headers';
import 'server-only';
import { Dictionary, Translate } from 'types/translation';
import { getDictionary } from 'utils/getDictionary';

export const getServerT = async (props?: {
    defaultDictionary?: Dictionary;
    defaultLang?: Locale;
}): Promise<Translate> => {
    let lang = props?.defaultLang;
    let dictionary = props?.defaultDictionary;

    if (!lang) {
        const headersList = await headers();
        const host = headersList.get('host');
        const domainConfig = getDomainConfig(host!);
        lang = domainConfig.defaultLocale;
    }

    if (!dictionary) {
        dictionary = await getDictionary(lang);
    }

    const { t } = createTranslation(dictionary, new Intl.PluralRules(lang));

    return t;
};
