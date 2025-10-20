import 'server-only';

const DEFAULT_LOCALE = 'en';

/**
 * Constructs internal GraphQL endpoint URL with proper locale path
 * Based on PR #4113 implementation for pages router
 *
 * @param internalEndpoint Base internal endpoint URL (e.g., "http://webserver:8080/")
 * @param locale Current locale to append to path (e.g., "sk")
 * @return Full internal GraphQL endpoint URL or undefined if no internal endpoint
 *
 * @example
 * getInternalGraphqlEndpoint("http://webserver:8080/", "en")
 * // Returns: "http://webserver:8080/graphql/"
 *
 * @example
 * getInternalGraphqlEndpoint("http://webserver:8080/", "sk")
 * // Returns: "http://webserver:8080/sk/graphql/"
 */
export const getInternalGraphqlEndpoint = (internalEndpoint: string | undefined, locale = DEFAULT_LOCALE) => {
    if (!internalEndpoint) {
        return undefined;
    }

    return `${internalEndpoint}${locale !== DEFAULT_LOCALE ? `${locale}/` : ''}graphql/`;
};

/**
 * Extracts locale from a domain URL with explicit path or returns default
 *
 * @param domainUrl Domain URL to analyze (e.g., "http://127.0.0.1:8000/sk/")
 * @return Extracted locale or DEFAULT_LOCALE if no explicit locale in path
 *
 * @example
 * getExplicitPathDomainLocaleOrDefault("http://127.0.0.1:8000/")
 * // Returns: "en"
 *
 * @example
 * getExplicitPathDomainLocaleOrDefault("http://127.0.0.1:8000/sk/")
 * // Returns: "sk"
 */
export const getExplicitPathDomainLocaleOrDefault = (domainUrl: string): string => {
    const normalizedUrl = domainUrl.endsWith('/') ? domainUrl.slice(0, -1) : domainUrl;
    const url = new URL(normalizedUrl);
    const pathSegments = url.pathname.split('/').filter(Boolean);

    // If there's a path segment, it's the locale
    if (pathSegments.length > 0) {
        return pathSegments[0];
    }

    return DEFAULT_LOCALE;
};
