import { ReactElement } from 'react';
import { TranslationKeys, TranslationQuery } from 'types/translation';

export interface TransProps {
    i18nKey: TranslationKeys;
    components?: ReactElement[] | Record<string, ReactElement>;
    values?: TranslationQuery;
    fallback?: string | string[];
    defaultTrans?: string;
}
