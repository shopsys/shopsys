import getConfig from 'next/config';

type PublicRuntimeConfig = { publicRuntimeConfig: { domains: DomainConfigType[] } };

const {
    publicRuntimeConfig: { domains: domainsConfig },
} = getConfig() as PublicRuntimeConfig;

export type DomainConfigType = {
    url: string;
    publicGraphqlEndpoint: string;
    defaultLocale: string;
    currencyCode: string;
    domainId: number;
    mapSetting: {
        latitude: number;
        longitude: number;
        zoom: number;
    };
    gtmId?: string;
};

export function getDomainConfig(domainUrl: string): DomainConfigType {
    const replacedDomain = domainUrl.replace(':3000', ':8000');

    for (const domain of domainsConfig) {
        const publicDomainUrl = new URL(domain.url || '').host;

        if (publicDomainUrl === replacedDomain) {
            return domain;
        }
    }

    throw new Error('Domain `' + replacedDomain + '` is not known domain');
}
