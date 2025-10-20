import { Dictionary, Translate, TranslationKeys, TranslationQuery } from 'types/translation';

const NAMESPACE_SEPARATOR = ':';
const DEFAULT_NAMESPACE = 'common';

export const createTranslation = (dictionary: Dictionary, pluralRules: Intl.PluralRules): { t: Translate } => {
    const t: Translate = (key: TranslationKeys, query?: TranslationQuery | null) => {
        const { namespace, translationKey } = parseNamespacedKey(key, query?.ns);
        const namespacedDictionary = getNamespaceDictionary(dictionary, namespace);
        const translationPluralKey = getPluralKey(namespacedDictionary, translationKey, pluralRules, query);
        const translationString =
            namespacedDictionary[translationPluralKey as keyof typeof namespacedDictionary] || key;

        return interpolation(translationString, query);
    };

    return { t };
};

const parseNamespacedKey = (key: string, nsOption?: string): { namespace: string; translationKey: string } => {
    if (nsOption) {
        return { namespace: nsOption, translationKey: key };
    }

    if (key.includes(NAMESPACE_SEPARATOR)) {
        const separatorIndex = key.indexOf(NAMESPACE_SEPARATOR);
        return {
            namespace: key.substring(0, separatorIndex),
            translationKey: key.substring(separatorIndex + 1),
        };
    }

    return { namespace: DEFAULT_NAMESPACE, translationKey: key };
};

const getNamespaceDictionary = (dictionary: Dictionary, namespace: string): Record<string, string> => {
    if (namespace in dictionary) {
        return dictionary[namespace as keyof Dictionary] as Record<string, string>;
    }

    return dictionary[DEFAULT_NAMESPACE] as Record<string, string>;
};

const interpolation = (text?: string, query?: TranslationQuery | null) => {
    if (!text || !query) {
        return text ?? '';
    }

    return text.replace(/{{\s*(\w+)\s*}}/g, (_: any, key: any) => query[key] || '');
};

const getPluralKey = (
    dictionary: Record<string, string>,
    key: string,
    pluralRules: Intl.PluralRules,
    query?: TranslationQuery | null,
) => {
    if (!query || typeof query.count !== 'number') {
        return key;
    }

    const count = query.count;

    const numKey = `${key}_${query.count}`;
    if (numKey in dictionary) {
        return numKey;
    }

    const pluralKey = `${key}_${pluralRules.select(count)}`;
    if (pluralKey in dictionary) {
        return pluralKey;
    }

    return key;
};
