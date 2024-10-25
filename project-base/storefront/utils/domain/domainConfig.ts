import domainsConfig from 'domains-config';
import { CustomerUserAreaEnum } from 'types/customer';

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

export function getDomainConfig(domainUrl: string): DomainConfigType {
    const replacedDomain = domainUrl.replace(':3000', ':8000');
    const cdnDomain = process.env.CDN_DOMAIN ?? '';

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
