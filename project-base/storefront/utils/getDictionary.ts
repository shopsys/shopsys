import { Locale } from 'i18n-config';
import 'server-only';
import { Dictionary } from 'types/translation';

// We enumerate all dictionaries here for better linting and typescript support
// We also get the default import for cleaner types
const dictionaries = {
    en: () => import('../public/locales/en/common.json').then((module) => module.default),
    sk: () => import('../public/locales/sk/common.json').then((module) => module.default),
    cs: () => import('../public/locales/cs/common.json').then((module) => module.default),
};

export const getDictionary = async (lang: Locale): Promise<Dictionary> => (await dictionaries[lang]()) as Dictionary;
