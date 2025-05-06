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
