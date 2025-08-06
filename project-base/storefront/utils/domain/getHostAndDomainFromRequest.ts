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
): { host: string; domainId: number; currentLocale: string | undefined } => {
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

    // Fallback: return first domain configuration
    const fallbackDomain = domainEntries[0];
    return {
        host: fallbackDomain.domainUrl + '/',
        domainId: fallbackDomain.domainId,
        currentLocale: fallbackDomain.locale,
    };
};

const removeTrailingSlash = (url: string) => {
    if (url.endsWith('/')) {
        return url.slice(0, -1);
    }

    return url;
};
