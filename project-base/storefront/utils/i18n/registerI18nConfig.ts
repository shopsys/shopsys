import i18nConfig from 'i18n';
import { I18nConfig } from 'next-translate';

export const resolvedI18nConfig = i18nConfig as I18nConfig;

export const registerI18nConfig = (): I18nConfig => {
    (globalThis as typeof globalThis & { i18nConfig?: I18nConfig }).i18nConfig = resolvedI18nConfig;

    return resolvedI18nConfig;
};
