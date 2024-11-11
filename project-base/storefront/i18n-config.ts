export const i18n = {
    defaultLocale: 'en',
    locales: ['en', 'cs', 'sk'],
} as const;

export type Locale = (typeof i18n)['locales'][number];
