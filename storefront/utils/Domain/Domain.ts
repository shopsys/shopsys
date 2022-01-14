import getConfig from 'next/config';

const { publicRuntimeConfig } = getConfig();

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
};

function getCurrentDomainFromWindow(): string {
    if (typeof window === 'undefined') {
        throw new Error('Impossible to get domain from window');
    }

    return window.location.host;
}

export function getDomainConfig(domain?: string): DomainConfigType {
    if (domain === undefined) {
        // eslint-disable-next-line no-param-reassign
        domain = getCurrentDomainFromWindow();
    }

    // Preventing error on localhost about unknown domain with SSR
    const replacedDomain = domain.replace(':3000', ':8000');

    for (const domainConfig of publicRuntimeConfig.domains) {
        const publicDomainUrl = new URL(domainConfig.url).host;

        if (publicDomainUrl === replacedDomain) {
            return domainConfig;
        }
    }

    throw new Error('Domain `' + replacedDomain + '` is not known domain');
}
