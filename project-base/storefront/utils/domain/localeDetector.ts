import type { DomainEntry } from './domainMatcher';
import { DEFAULT_LOCALE } from './domainUtils';

export const findDomainByBrowserLocale = (domainsWithSameHost: DomainEntry[], acceptLanguage: string): DomainEntry => {
    const browserLocales = parseBrowserLocales(acceptLanguage);

    for (const browserLocale of browserLocales) {
        const matchingDomain = domainsWithSameHost.find((domain) => {
            // Only match explicit locale paths
            if (domain.locale === DEFAULT_LOCALE) {
                return false;
            }

            return domain.locale?.toLowerCase() === browserLocale.toLowerCase();
        });

        if (matchingDomain) {
            return matchingDomain;
        }
    }

    return domainsWithSameHost[0];
};

/**
 * Parse Accept-Language header to extract browser preferred locales
 * @param acceptLanguage Accept-Language header value
 * @return Array of locale codes sorted by preference
 */
const parseBrowserLocales = (acceptLanguage: string): string[] => {
    if (!acceptLanguage) {
        return [];
    }

    const locales = acceptLanguage
        .split(',')
        .map((lang) => {
            const [locale, qValue] = lang.trim().split(';q=');
            const quality = qValue ? parseFloat(qValue) : 1.0;
            // Extract the language code (e.g., 'cs' from 'cs-CZ')
            const languageCode = locale.split('-')[0].toLowerCase();
            return { locale: languageCode, quality };
        })
        .sort((a, b) => b.quality - a.quality)
        .map((item) => item.locale);

    // Remove duplicates
    return Array.from(new Set(locales));
};
