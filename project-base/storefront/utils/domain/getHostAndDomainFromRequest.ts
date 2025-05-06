import { DEFAULT_LOCALE, getBaseUrlWithLocale } from './domainUtils';
import { STATIC_REWRITE_PATHS } from 'config/staticRewritePaths';
import type { NextRequest } from 'next/server';

export type LocaleInfo = {
    domainId: number;
    locale: string | undefined;
    hasLocalePrefix: boolean;
};

export const getHostAndDomainFromRequest = (
    request: NextRequest,
): { host: string; domainId: number; currentLocale: string | undefined } => {
    let domainId = -1;
    const requestHeaders = new Headers(request.headers);
    const availableLocales = Object.keys(STATIC_REWRITE_PATHS).map((domainUrl) => {
        const normalizedDomainUrl = removeTrailingSlash(domainUrl);
        const urlSegments = normalizedDomainUrl.split('/');
        const locale = urlSegments[urlSegments.length - 1];

        return locale.length > 0 ? locale : DEFAULT_LOCALE;
    });
    const currentLocale = availableLocales.find((locale, index) => {
        if (!locale) {
            return false;
        }

        const regex = new RegExp(`/${locale}(?:$|/)`);
        if (regex.test(request.url)) {
            domainId = index + 1;

            return true;
        }

        return false;
    });
    if (requestHeaders.get('host') === null) {
        throw new Error(`Host was not found in the request header.`);
    }

    const host = getBaseUrlWithLocale(requestHeaders.get('host')!, currentLocale);

    return { host, domainId, currentLocale };
};

const removeTrailingSlash = (url: string) => {
    if (url.endsWith('/')) {
        return url.slice(0, -1);
    }

    return url;
};
