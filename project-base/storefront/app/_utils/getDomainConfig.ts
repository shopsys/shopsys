import domainsConfig from 'domains-config';
import { Locale } from 'i18n-config';
import { headers } from 'next/headers';
import { cache } from 'react';
import 'server-only';
import { CustomerUserAreaEnum } from 'types/customer';

export type DomainConfigType = {
    url: string;
    publicGraphqlEndpoint: string;
    defaultLocale: Locale;
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

/**
 * Gets domain configuration from middleware headers or by matching host
 * Middleware performs domain resolution with path fragment support
 *
 * @return Matched domain configuration
 */
export const getDomainConfig = cache(async (): Promise<DomainConfigType> => {
    const headersList = await headers();

    // Try to get domain info from middleware headers (set by getHostAndDomainFromRequest)
    const domainIdHeader = headersList.get('x-domain-id');
    const domainUrlHeader = headersList.get('x-domain-url');

    // If middleware provided domain info, use it
    if (domainIdHeader) {
        const domainId = parseInt(domainIdHeader, 10);
        const domainConfig = domainsConfig.find((config) => config.domainId === domainId);
        if (domainConfig) {
            return domainConfig;
        }
    }

    // If middleware provided domain URL, try to match by URL
    if (domainUrlHeader) {
        const normalizedUrl = domainUrlHeader.endsWith('/') ? domainUrlHeader : domainUrlHeader + '/';
        const domainConfig = domainsConfig.find((config) => {
            const configUrl = config.url.endsWith('/') ? config.url : config.url + '/';
            return configUrl === normalizedUrl;
        });
        if (domainConfig) {
            return domainConfig;
        }
    }

    // Fallback: use host header for basic matching (for direct app router access without middleware)
    const host = headersList.get('host');
    if (host) {
        const normalizedHost = host.replace(':3000', ':8000');

        // Fallback for internal Docker network requests
        if (normalizedHost.includes('webserver:') || normalizedHost.includes('storefront:')) {
            return domainsConfig[0];
        }

        // Try to match by host
        const domainConfig = domainsConfig.find((config) => {
            const configUrl = new URL(config.url);
            return configUrl.host === normalizedHost;
        });

        if (domainConfig) {
            return domainConfig;
        }
    }

    // Ultimate fallback - return first domain
    if (domainsConfig.length > 0) {
        return domainsConfig[0];
    }

    throw new Error('No domain configuration available');
});
