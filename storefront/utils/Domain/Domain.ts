import getConfig from 'next/config';

const { publicRuntimeConfig } = getConfig();

export type DomainConfigType = {
    domain: string;
    publicGraphqlEndpoint: string;
    defaultLocale: string;
    currencyCode: string;
};

function getCurrentDomainFromWindow(): string {
    if (window === undefined || window.location.host === undefined) {
        throw new Error('Impossible to get domain from window');
    }

    return window.location.host;
}

export function getDomainConfig(domain?: string): DomainConfigType {
    if (domain === undefined) {
        // eslint-disable-next-line no-param-reassign
        domain = getCurrentDomainFromWindow();
    }
    for (const domainConfig of publicRuntimeConfig.domains) {
        if (domainConfig.domain === domain) {
            return domainConfig;
        }
    }

    throw new Error('unknown domain');
}
