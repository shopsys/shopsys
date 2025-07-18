import getConfig from 'next/config';
import { CustomerUserAreaEnum } from 'types/customer';
import { domainConfigDebug } from 'utils/debug/logger';

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
};

export function getDomainConfig(domainUrl: string): DomainConfigType {
    const replacedDomain = domainUrl.replace(':3000', ':8000');

    // Debug the input
    domainConfigDebug.log('🔍 getDomainConfig called with:', { domainUrl, replacedDomain });
    domainConfigDebug.log('🔍 domainsConfig:', domainsConfig);
    domainConfigDebug.log('🔍 cdnDomain:', cdnDomain);

    for (const domain of domainsConfig) {
        // Debug each domain before URL creation
        domainConfigDebug.log('🔍 Processing domain:', domain);
        domainConfigDebug.log('🔍 domain.url value:', domain.url, 'type:', typeof domain.url);

        // Add safety check to prevent empty URL
        if (!domain.url || domain.url.trim() === '') {
            domainConfigDebug.warn('⚠️ Skipping domain with empty URL:', domain);
            continue;
        }

        const publicDomainUrl = new URL(domain.url).host;
        domainConfigDebug.log('🔍 publicDomainUrl:', publicDomainUrl);

        if (publicDomainUrl === replacedDomain) {
            domainConfigDebug.log('✅ Match found, returning domain:', domain);
            return domain;
        }
    }

    domainConfigDebug.log("🚀 -> domainConfig.ts -> getDomainConfig -> replacedDomain:", replacedDomain)

    // Debug CDN domain before URL creation
    domainConfigDebug.log('🔍 Checking CDN domain:', cdnDomain);
    if (!cdnDomain || cdnDomain.trim() === '') {
        domainConfigDebug.error('❌ CDN domain is empty or undefined');
        throw new Error('CDN domain is not configured properly');
    }

    // Return first domain for CDN domain to properly render error page
    const cdnDomainHost = new URL(cdnDomain).host;
    if (replacedDomain === cdnDomainHost) {
        return domainsConfig[0];
    }

    throw new Error('Domain `' + replacedDomain + '` is not known domain');
}
