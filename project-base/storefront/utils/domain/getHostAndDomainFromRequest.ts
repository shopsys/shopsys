import type { NextRequest } from 'next/server';
import { findDomainByPath, getDomainEntries } from './domainMatcher';
import { findDomainByBrowserLocale } from './localeDetector';
import { parseRequest } from './requestParser';

export const getHostAndDomainFromRequest = (
    request: NextRequest,
): { host: string; domainId: number; currentLocale: string | undefined; redirect?: boolean } => {
    const parsedRequest = parseRequest(request);
    const domainEntries = getDomainEntries();

    // Step 1: Try to find domain by path matching
    const matchedDomain = findDomainByPath(domainEntries, parsedRequest.requestBaseUrl, parsedRequest.requestPath);

    if (matchedDomain) {
        return {
            host: `${matchedDomain.domainUrl}/`,
            domainId: matchedDomain.domainId,
            currentLocale: matchedDomain.locale,
        };
    }

    // Step 2: Fallback - find domains with same host and use browser locale detection
    const requestUrlObj = new URL(parsedRequest.requestBaseUrl);
    const domainsWithSameHost = domainEntries.filter((entry) => entry.host === requestUrlObj.host);

    if (domainsWithSameHost.length > 0) {
        const targetDomain = findDomainByBrowserLocale(domainsWithSameHost, parsedRequest.acceptLanguage);

        // Found a domain with the same host - redirect to it
        return {
            host: `${targetDomain.domainUrl}/`,
            domainId: targetDomain.domainId,
            currentLocale: targetDomain.locale,
            redirect: true,
        };
    }

    // Step 3: Ultimate fallback - should not happen as nginx should handle other domains
    const ultimateFallback = domainEntries[0];
    return {
        host: `${ultimateFallback.domainUrl}/`,
        domainId: ultimateFallback.domainId,
        currentLocale: ultimateFallback.locale,
        redirect: true,
    };
};
