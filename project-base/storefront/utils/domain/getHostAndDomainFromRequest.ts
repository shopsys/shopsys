import { DEFAULT_LOCALE, getExplicitPathDomainLocaleOrDefault } from './domainUtils';
import { STATIC_REWRITE_PATHS } from 'config/staticRewritePaths';
import type { NextRequest } from 'next/server';

export type LocaleInfo = {
    domainId: number;
    locale: string | undefined;
    hasLocalePrefix: boolean;
};

export const getHostAndDomainFromRequest = (
    request: NextRequest,
): { host: string; domainId: number; currentLocale: string | undefined; redirect?: boolean } => {
    const requestHeaders = new Headers(request.headers);
    const requestHost = requestHeaders.get('host');

    if (!requestHost) {
        throw new Error('Host was not found in the request header.');
    }

    const protocol = requestHeaders.get('x-forwarded-proto') || 'http';
    const requestUrl = `${protocol}://${requestHost}`;

    // Use original URL pathname instead of nextUrl.pathname to avoid locale stripping
    const originalUrl = new URL(request.url);
    const requestPath = originalUrl.pathname;

    // Extract domain configurations and locales from STATIC_REWRITE_PATHS
    const domainEntries = Object.keys(STATIC_REWRITE_PATHS).map((domainUrl, index) => {
        const normalizedDomainUrl = removeTrailingSlash(domainUrl);
        const locale = getExplicitPathDomainLocaleOrDefault(normalizedDomainUrl);

        return {
            domainUrl: normalizedDomainUrl,
            locale,
            domainId: index + 1,
        };
    });

    // Step 1: Filter domains by matching host and sort by path length (longest first)
    // This ensures more specific paths like /sk are checked before root path /
    const requestUrlObj = new URL(requestUrl);
    const matchingDomains = domainEntries
        .filter((entry) => {
            const domainUrlObj = new URL(entry.domainUrl);
            return domainUrlObj.host === requestUrlObj.host;
        })
        .sort((a, b) => {
            const aPath = new URL(a.domainUrl).pathname;
            const bPath = new URL(b.domainUrl).pathname;
            // Sort by path length descending (longest first)
            return bPath.length - aPath.length;
        });

    // Step 2: Find the first matching domain (will be the most specific due to sorting)
    for (const domainEntry of matchingDomains) {
        const domainUrlObj = new URL(domainEntry.domainUrl);
        const domainPath = domainUrlObj.pathname;

        // Check if request path matches domain path
        if (domainPath === '/') {
            // Root path matches everything
            return {
                host: domainEntry.domainUrl + '/',
                domainId: domainEntry.domainId,
                currentLocale: domainEntry.locale,
            };
        }

        // For non-root paths, check exact match or prefix match
        if (
            requestPath === domainPath ||
            requestPath.startsWith(domainPath + '/') ||
            requestPath + '/' === domainPath + '/'
        ) {
            return {
                host: domainEntry.domainUrl + '/',
                domainId: domainEntry.domainId,
                currentLocale: domainEntry.locale,
            };
        }
    }

    // Step 3: Fallback - only if no exact domain match was found
    // This handles cases where user accesses base host that has no exact domain configuration
    // but there are domains with the same host + locale paths
    const domainsWithSameHost = domainEntries.filter((entry) => {
        const domainUrlObj = new URL(entry.domainUrl);
        return domainUrlObj.host === requestUrlObj.host;
    });

    if (domainsWithSameHost.length > 0) {
        // Get browser preferred locales from Accept-Language header
        const acceptLanguage = requestHeaders.get('accept-language') || '';
        const browserLocales = parseBrowserLocales(acceptLanguage);

        // Try to find a domain matching browser's preferred locale
        let targetDomain = domainsWithSameHost[0]; // Default to first domain

        for (const browserLocale of browserLocales) {
            const matchingDomain = domainsWithSameHost.find((domain) => {
                // Only match explicit locale paths
                if (domain.locale === DEFAULT_LOCALE) {
                    return false;
                }

                return domain.locale.toLowerCase() === browserLocale.toLowerCase();
            });

            if (matchingDomain) {
                targetDomain = matchingDomain;
                break;
            }
        }

        // Found a domain with the same host - redirect to it
        return {
            host: targetDomain.domainUrl + '/',
            domainId: targetDomain.domainId,
            currentLocale: targetDomain.locale,
            redirect: true,
        };
    }

    // This should not happen as nginx should handle other domains
    // But keeping the ultimate fallback for safety
    const ultimateFallback = domainEntries[0];
    return {
        host: ultimateFallback.domainUrl + '/',
        domainId: ultimateFallback.domainId,
        currentLocale: ultimateFallback.locale,
        redirect: true,
    };
};

const removeTrailingSlash = (url: string) => {
    if (url.endsWith('/')) {
        return url.slice(0, -1);
    }

    return url;
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
