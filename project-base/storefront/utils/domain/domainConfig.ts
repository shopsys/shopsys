import { getBaseUrlWithLocale } from './domainUtils';
import getConfig from 'next/config';
import { CustomerUserAreaEnum } from 'types/customer';

export type PublicRuntimeConfig = { publicRuntimeConfig: { domains: DomainConfigType[]; cdnDomain: string } };

const {
    publicRuntimeConfig: { domains: domainsConfig, cdnDomain },
} = getConfig() as PublicRuntimeConfig;

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

export function getDomainConfig(domainUrl: string, locale: string | undefined): DomainConfigType {
    const normalizedDomain = domainUrl.replace(':3000', ':8000');
    const hostWithLocale = getBaseUrlWithLocale(normalizedDomain, locale);
    const isDefaultLocale = locale === 'default';

    for (const domainConfig of domainsConfig) {
        const configDomainHost = new URL(domainConfig.url || '').host;

        if (!isDefaultLocale && domainConfig.defaultLocale === locale) {
            if (configDomainHost === normalizedDomain) {
                return domainConfig;
            }
            // Skip if domain hosts don't match to avoid locale conflicts
            continue;
        }

        if (isDefaultLocale && configDomainHost === normalizedDomain) {
            return domainConfig;
        }
    }

    const cdnDomainHost = getBaseUrlWithLocale(new URL(cdnDomain).host, locale);
    if (hostWithLocale === cdnDomainHost) {
        return domainsConfig[0];
    }

    throw new Error(`Domain '${hostWithLocale}' is not configured`);
}
