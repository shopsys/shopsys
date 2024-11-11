'use client';

import { Locale } from 'i18n-config';
import { ReactNode, createContext, useContext, useMemo } from 'react';
import { Dictionary, Translate } from 'types/translation';
import { createTranslation } from 'utils/translation';

export type TranslationContext = {
    dictionary: Dictionary;
    lang: Locale;
};

const TranslationContext = createContext<TranslationContext | null>(null);

export const useTranslation = () => {
    const context = useContext(TranslationContext);

    if (!context) {
        throw new Error('useTranslation must be used within a TranslationProvider');
    }

    const t = useMemo<Translate>(() => {
        const { t } = createTranslation(context.dictionary, new Intl.PluralRules(context.lang));
        return t;
    }, [context.dictionary]);

    return { t, lang: context.lang };
};

export function TranslationProvider({
    children,
    dictionary,
    lang,
}: {
    children: ReactNode;
    dictionary: Dictionary;
    lang: Locale;
}) {
    return (
        <TranslationContext.Provider
            value={{
                dictionary,
                lang,
            }}
        >
            {children}
        </TranslationContext.Provider>
    );
}
