import { Locale } from 'i18n-config';
import 'server-only';
import { Dictionary } from 'types/translation';

const namespaces = ['common', 'accessibility'] as const;

type NamespaceDictionaries = {
    [key in (typeof namespaces)[number]]: () => Promise<Record<string, string>>;
};

const dictionariesByNamespace: Record<Locale, NamespaceDictionaries> = {
    en: {
        common: () => import('../public/locales/en/common.json').then((module) => module.default),
        accessibility: () => import('../public/locales/en/accessibility.json').then((module) => module.default),
    },
    sk: {
        common: () => import('../public/locales/sk/common.json').then((module) => module.default),
        accessibility: () => import('../public/locales/sk/accessibility.json').then((module) => module.default),
    },
    cs: {
        common: () => import('../public/locales/cs/common.json').then((module) => module.default),
        accessibility: () => import('../public/locales/cs/accessibility.json').then((module) => module.default),
    },
};

export const getDictionary = async (lang: Locale): Promise<Dictionary> => {
    const namespaceDictionaries = dictionariesByNamespace[lang];
    const loadedDictionaries = await Promise.all(
        namespaces.map(async (namespace) => [namespace, await namespaceDictionaries[namespace]()]),
    );

    return Object.fromEntries(loadedDictionaries) as Dictionary;
};
