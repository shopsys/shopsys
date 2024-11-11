import { Dictionary, Translate, TranslationKeys, TranslationQuery } from 'types/translation';

export const createTranslation = (dictionary: Dictionary, pluralRules: Intl.PluralRules): { t: Translate } => {
    const t: Translate = (key: TranslationKeys, query?: TranslationQuery | null) => {
        const translationPluralKey = getPluralKey(dictionary, key, pluralRules, query);
        const translationString = dictionary[translationPluralKey as keyof Dictionary] || key;

        return interpolation(translationString, query);
    };

    return { t };
};

const interpolation = (text?: string, query?: TranslationQuery | null) => {
    if (!text || !query) {
        return text ?? '';
    }

    return text.replace(/{{\s*(\w+)\s*}}/g, (_: any, key: any) => query[key] || '');
};

const getPluralKey = (
    dictionary: Dictionary,
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
