export const DEFAULT_LOCALE = 'default';

export const getBaseUrlWithLocale = (baseUrl: string, locale = DEFAULT_LOCALE) => {
    const domainWithLocale = `${baseUrl}${locale !== DEFAULT_LOCALE ? `/${locale}` : ''}`;
    return domainWithLocale;
};

export const getBasePathWithLocale = (basePath: string, locale = DEFAULT_LOCALE) => {
    return `${locale !== DEFAULT_LOCALE ? `/${locale}` : ''}${basePath}`;
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
