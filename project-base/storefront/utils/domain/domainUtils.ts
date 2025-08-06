export const DEFAULT_LOCALE = 'default';

export const getBaseUrlWithLocale = (baseUrl: string, locale = DEFAULT_LOCALE) => {
    return `${baseUrl}${locale !== DEFAULT_LOCALE ? `/${locale}` : ''}`;
};

export const getBasePathWithLocale = (basePath: string, locale = DEFAULT_LOCALE) => {
    const normalizedBasePath = basePath.startsWith('/') ? basePath : `/${basePath}`;

    return `${locale !== DEFAULT_LOCALE ? `/${locale}` : ''}${normalizedBasePath}`;
};

export const getInternalGraphqlEndpoint = (internalEndpoint: string | undefined, locale = DEFAULT_LOCALE) => {
    if (!internalEndpoint) {
        return undefined;
    }

    return `${internalEndpoint}${locale !== DEFAULT_LOCALE ? `${locale}/` : ''}graphql/`;
};

export const getHostFromDomain = (domain: string): string => {
    const url = new URL(domain);
    return `${url.protocol}//${url.host}/`;
};

/**
 * Extracts locale from a domain URL with explicit path or returns default
 *
 * @param domainUrl Domain URL to analyze (e.g., "http://127.0.0.1:8000/sk")
 * @return Extracted locale or DEFAULT_LOCALE if no explicit locale in path
 */
export const getExplicitPathDomainLocaleOrDefault = (domainUrl: string): string => {
    const normalizedUrl = domainUrl.endsWith('/') ? domainUrl.slice(0, -1) : domainUrl;
    const urlSegments = normalizedUrl.split('/');
    return urlSegments.length > 3 ? urlSegments[urlSegments.length - 1] : DEFAULT_LOCALE;
};
