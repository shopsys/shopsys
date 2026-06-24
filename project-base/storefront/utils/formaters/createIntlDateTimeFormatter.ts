let currentLocale = 'en';

export const initIntlDateTimeFormatterLocale = (defaultLocale: string): void => {
    currentLocale = defaultLocale;
};

export const createIntlDateTimeFormatter = (
    options: Intl.DateTimeFormatOptions,
    timezone?: string,
    locale?: string,
): Intl.DateTimeFormat =>
    new Intl.DateTimeFormat(locale ?? currentLocale, {
        ...options,
        timeZone: timezone,
    });
