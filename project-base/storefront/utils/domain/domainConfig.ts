import { getBaseUrlWithLocale, DEFAULT_LOCALE } from './domainUtils';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { CustomerUserAreaEnum } from 'types/customer';
import { getPublicConfigProperty } from 'utils/config/getNextConfig';

const domainsConfig: DomainConfigType[] = getPublicConfigProperty('domains', []) as DomainConfigType[];
const cdnDomain = getPublicConfigProperty('cdnDomain', '');

export type DomainConfigType = {
    url: string;
    publicGraphqlEndpoint: string;
    defaultLocale: string;
    currencyCode: string;
    fallbackTimezone: string;
    domainId: number;
    mapSetting: {
        latitude: number;
        longitude: number;
        zoom: number;
    };
    gtmId?: string;
    isLuigisBoxActive: boolean;
    type: CustomerUserAreaEnum;
};

export function getDomainConfig(context: GetServerSidePropsContext | NextPageContext): DomainConfigType {
    if (!context.req?.headers.host) {
        throw new Error('getDomainConfig requires server-side context with req.headers.host');
    }

    const domainUrl = context.req.headers.host;
    const locale = context.locale;

    const normalizedDomain = domainUrl.replace(':3000', ':8000');
    const hostWithLocale = getBaseUrlWithLocale(normalizedDomain, locale);
    const isDefaultLocale = locale === DEFAULT_LOCALE;

    for (const domainConfig of domainsConfig) {
        const configDomainUrl = new URL(domainConfig.url || '');
        const configDomainHost = configDomainUrl.host;
        const configDomainPath = configDomainUrl.pathname;

        // For non-default locales, match both host and locale
        if (!isDefaultLocale && domainConfig.defaultLocale === locale) {
            const requestUrl = new URL(`http://${normalizedDomain}`);

            // Check if hosts match
            if (configDomainHost === requestUrl.host) {
                // For domains with locale in path (like /sk/), check if config path matches locale
                if (configDomainPath !== '/' && configDomainPath.startsWith(`/${locale}`)) {
                    return domainConfig;
                }
                // For domains without path but matching locale
                if (configDomainPath === '/') {
                    return domainConfig;
                }
            }
            continue;
        }

        // For default locale, match host and ensure no conflicting locale path
        if (isDefaultLocale) {
            const requestUrl = new URL(`http://${normalizedDomain}`);

            if (configDomainHost === requestUrl.host) {
                // Prefer domains without locale path for default locale
                if (configDomainPath === '/' || configDomainPath === '') {
                    return domainConfig;
                }
            }
        }
    }

    // Fallback for default locale when only locale-suffixed domains exist
    if (isDefaultLocale) {
        const requestUrl = new URL(`http://${normalizedDomain}`);
        const fallbackDomain = domainsConfig.find((domainConfig) => {
            const configDomainHost = new URL(domainConfig.url || '').host;
            return configDomainHost === requestUrl.host;
        });

        if (fallbackDomain) {
            return fallbackDomain;
        }
    }

    if (cdnDomain.length > 0) {
        const cdnDomainHost = getBaseUrlWithLocale(new URL(cdnDomain).host, locale);
        if (hostWithLocale === cdnDomainHost) {
            return domainsConfig[0];
        }
    }

    throw new Error(`Domain '${hostWithLocale}' is not configured`);
}
