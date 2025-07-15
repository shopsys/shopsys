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
};

export function getDomainConfig(domainUrl: string): DomainConfigType {
    const replacedDomain = domainUrl.replace(':3000', ':8000');

    // Debug the input
    console.log('🔍 getDomainConfig called with:', { domainUrl, replacedDomain });
    console.log('🔍 domainsConfig:', domainsConfig);
    console.log('🔍 cdnDomain:', cdnDomain);

    for (const domain of domainsConfig) {
        // Debug each domain before URL creation
        console.log('🔍 Processing domain:', domain);
        console.log('🔍 domain.url value:', domain.url, 'type:', typeof domain.url);

        // Add safety check to prevent empty URL
        if (!domain.url || domain.url.trim() === '') {
            console.warn('⚠️ Skipping domain with empty URL:', domain);
            //continue;
        }

        const publicDomainUrl = new URL(domain.url).host;
        console.log('🔍 publicDomainUrl:', publicDomainUrl);

        if (publicDomainUrl === replacedDomain) {
            console.log('✅ Match found, returning domain:', domain);
            return domain;
        }
    }

    console.log("🚀 -> domainConfig.ts -> getDomainConfig -> replacedDomain:", replacedDomain)

    // Debug CDN domain before URL creation
    console.log('🔍 Checking CDN domain:', cdnDomain);
    if (!cdnDomain || cdnDomain.trim() === '') {
        console.error('❌ CDN domain is empty or undefined');
        //throw new Error('CDN domain is not configured properly');
    }

    // Return first domain for CDN domain to properly render error page
    const cdnDomainHost = new URL(cdnDomain/*  || domainsConfig[0].url */).host;
    if (replacedDomain === cdnDomainHost) {
        return domainsConfig[0];
    }

    throw new Error('Domain `' + replacedDomain + '` is not known domain');
}
