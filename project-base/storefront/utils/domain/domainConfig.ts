import { DomainConfig, getPublicConfigProperty } from 'envConfig';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { DEFAULT_LOCALE, getBaseUrlWithLocale } from './domainUtils';

const domainsConfig = getPublicConfigProperty('domains');
const cdnDomain = getPublicConfigProperty('cdnDomain');

export type DomainConfigType = DomainConfig;

type DomainConfigResolutionResult = {
    domainConfig?: DomainConfigType;
    hostWithLocale: string;
};

const getFirstHeaderValue = (headerValue: string | string[] | undefined): string | undefined => {
    const value = Array.isArray(headerValue) ? headerValue[0] : headerValue;

    return value?.split(',')[0]?.trim() || undefined;
};

const getRequestProtocol = (context: GetServerSidePropsContext | NextPageContext): string => {
    return getFirstHeaderValue(context.req?.headers['x-forwarded-proto'])?.toLowerCase() === 'https' ? 'https' : 'http';
};

const normalizeDomainHost = (domainUrl: string, protocol: string): string => {
    const normalizedDomain = domainUrl.replace(/:3000$/, ':8000');
    const requestUrl = normalizedDomain.includes('://')
        ? new URL(normalizedDomain)
        : new URL(`${protocol}://${normalizedDomain}`);

    return requestUrl.host;
};

const findDomainConfigByHost = (host: string): DomainConfigType | undefined => {
    const matchingDomainConfigs = domainsConfig.filter((domainConfig) => new URL(domainConfig.url || '').host === host);

    return (
        matchingDomainConfigs.find((domainConfig) => {
            const configDomainPath = new URL(domainConfig.url || '').pathname;

            return configDomainPath === '/' || configDomainPath === '';
        }) || matchingDomainConfigs[0]
    );
};

export const resolveDomainConfigByHost = (
    domainUrl: string,
    locale: string = DEFAULT_LOCALE,
    protocol: string = 'http',
): DomainConfigResolutionResult => {
    const normalizedDomain = normalizeDomainHost(domainUrl, protocol);
    const hostWithLocale = getBaseUrlWithLocale(normalizedDomain, locale);
    const isDefaultLocale = locale === DEFAULT_LOCALE;
    const requestUrl = new URL(`${protocol}://${normalizedDomain}`);

    for (const domainConfig of domainsConfig) {
        const configDomainUrl = new URL(domainConfig.url || '');
        const configDomainHost = configDomainUrl.host;
        const configDomainPath = configDomainUrl.pathname;

        // For non-default locales, match both host and locale
        if (!isDefaultLocale && domainConfig.defaultLocale === locale) {
            // Check if hosts match
            if (configDomainHost === requestUrl.host) {
                // For domains with locale in path (like /sk/), check if config path matches locale
                if (configDomainPath !== '/' && configDomainPath.startsWith(`/${locale}`)) {
                    return { domainConfig, hostWithLocale };
                }
                // For domains without path but matching locale
                if (configDomainPath === '/') {
                    return { domainConfig, hostWithLocale };
                }
            }
            continue;
        }

        // For default locale, match host and ensure no conflicting locale path
        if (isDefaultLocale) {
            if (configDomainHost === requestUrl.host) {
                // Prefer domains without locale path for default locale
                if (configDomainPath === '/' || configDomainPath === '') {
                    return { domainConfig, hostWithLocale };
                }
            }
        }
    }

    // Unsupported locale paths should still use the configured domain for the host
    // and continue through the standard storefront routing/error handling.
    const hostFallbackDomain = findDomainConfigByHost(requestUrl.host);
    if (hostFallbackDomain) {
        return { domainConfig: hostFallbackDomain, hostWithLocale };
    }

    if (cdnDomain.length > 0) {
        const cdnDomainHost = getBaseUrlWithLocale(new URL(cdnDomain).host, locale);
        if (hostWithLocale === cdnDomainHost) {
            return { domainConfig: domainsConfig[0], hostWithLocale };
        }
    }

    return { hostWithLocale };
};

export function getDomainConfig(context: GetServerSidePropsContext | NextPageContext): DomainConfigType {
    const requestHost =
        getFirstHeaderValue(context.req?.headers['x-forwarded-host']) ?? getFirstHeaderValue(context.req?.headers.host);

    if (!requestHost) {
        throw new Error('getDomainConfig requires server-side context with req.headers.host');
    }

    const { domainConfig, hostWithLocale } = resolveDomainConfigByHost(
        requestHost,
        context.locale,
        getRequestProtocol(context),
    );

    if (domainConfig) {
        return domainConfig;
    }

    throw new Error(`Domain '${hostWithLocale}' is not configured`);
}
