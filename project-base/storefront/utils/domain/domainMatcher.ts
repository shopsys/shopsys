import { STATIC_REWRITE_PATHS } from 'config/staticRewritePaths';
import { getExplicitPathDomainLocaleOrDefault } from './domainUtils';

export type DomainEntry = {
    domainUrl: string;
    locale: string | undefined;
    domainId: number;
    host: string;
    pathname: string;
};

export const getDomainEntries = (): DomainEntry[] => {
    return Object.keys(STATIC_REWRITE_PATHS).map((domainUrl, index) => {
        const normalizedDomainUrl = removeTrailingSlash(domainUrl);
        const locale = getExplicitPathDomainLocaleOrDefault(normalizedDomainUrl);
        const domainUrlObj = new URL(normalizedDomainUrl);

        return {
            domainUrl: normalizedDomainUrl,
            locale,
            domainId: index + 1,
            host: domainUrlObj.host,
            pathname: domainUrlObj.pathname,
        };
    });
};

export const findDomainByPath = (
    domainEntries: DomainEntry[],
    requestBaseUrl: string,
    requestPath: string,
): DomainEntry | undefined => {
    const requestUrlObj = new URL(requestBaseUrl);

    // Filter domains by matching host and sort by path length (longest first)
    // This ensures more specific paths like /sk are checked before root path /
    const matchingDomains = domainEntries
        .filter((entry) => entry.host === requestUrlObj.host)
        .sort((a, b) => {
            // Sort by path length descending (longest first)
            return b.pathname.length - a.pathname.length;
        });

    // Find the first matching domain (will be the most specific due to sorting)
    for (const domainEntry of matchingDomains) {
        if (isPathMatch(domainEntry.pathname, requestPath)) {
            return domainEntry;
        }
    }

    return undefined;
};

const isPathMatch = (domainPath: string, requestPath: string): boolean => {
    // Check if request path matches domain path
    if (domainPath === '/') {
        // Root path matches everything
        return true;
    }

    // For non-root paths, check exact match or prefix match
    return (
        requestPath === domainPath || requestPath.startsWith(`${domainPath}/`) || `${requestPath}/` === `${domainPath}/`
    );
};

const removeTrailingSlash = (url: string) => {
    if (url.endsWith('/')) {
        return url.slice(0, -1);
    }

    return url;
};
