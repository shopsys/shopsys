import getConfig from 'next/config';
import { CustomerUserAreaEnum } from 'types/customer';

type PublicRuntimeConfig = { publicRuntimeConfig: { domains: DomainConfigType[]; cdnDomain: string } };

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
    shouldUseConvertim: boolean;
};

export function getDomainConfig(domainUrl: string): DomainConfigType {
    const replacedDomain = domainUrl.replace(':3000', ':8000');

    for (const domain of domainsConfig) {
        const publicDomainUrl = new URL(domain.url || '').host;

        if (publicDomainUrl === replacedDomain) {
            return domain;
        }
    }

    // Return first domain for CDN domain to properly render error page
    const cdnDomainHost = new URL(cdnDomain).host;
    if (replacedDomain === cdnDomainHost) {
        return domainsConfig[0];
    }

    throw new Error('Domain `' + replacedDomain + '` is not known domain');
}
