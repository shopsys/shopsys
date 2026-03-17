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

export const resolveDomainConfigByHost = (
    domainUrl: string,
    locale: string = DEFAULT_LOCALE,
): DomainConfigResolutionResult => {
    const normalizedDomain = domainUrl.replace(':3000', ':8000');
    const hostWithLocale = getBaseUrlWithLocale(normalizedDomain, locale);
    const isDefaultLocale = locale === DEFAULT_LOCALE;
    const requestUrl = new URL(`http://${normalizedDomain}`);

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

    // Fallback for default locale when only locale-suffixed domains exist
    if (isDefaultLocale) {
        const fallbackDomain = domainsConfig.find((domainConfig) => {
            const configDomainHost = new URL(domainConfig.url || '').host;
            return configDomainHost === requestUrl.host;
        });

        if (fallbackDomain) {
            return { domainConfig: fallbackDomain, hostWithLocale };
        }
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
    if (!context.req?.headers.host) {
        throw new Error('getDomainConfig requires server-side context with req.headers.host');
    }

    const { domainConfig, hostWithLocale } = resolveDomainConfigByHost(context.req.headers.host, context.locale);

    if (domainConfig) {
        return domainConfig;
    }

    throw new Error(`Domain '${hostWithLocale}' is not configured`);
}
