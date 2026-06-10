import { CustomerUserAreaEnum } from 'types/customer';
import type { PublicRuntimeConfig } from './envConfig';

export function buildPublicConfig(): PublicRuntimeConfig {
    const sentryDsn = process.env.SENTRY_DSN?.trim() ?? '';

    const domainUrl1 = process.env.DOMAIN_HOSTNAME_1 ?? '';
    const domainUrl2 = process.env.DOMAIN_HOSTNAME_2 ?? '';
    const domainUrl3 = process.env.DOMAIN_HOSTNAME_3 ?? '';

    const domainUrls = { DOMAIN_HOSTNAME_1: domainUrl1, DOMAIN_HOSTNAME_2: domainUrl2, DOMAIN_HOSTNAME_3: domainUrl3 };

    for (const [envVar, url] of Object.entries(domainUrls)) {
        if (!url) {
            throw new Error(`${envVar} is required but not set.`);
        }
        if (!URL.canParse(url)) {
            throw new Error(`${envVar}="${url}" is not a valid URL.`);
        }
    }

    return {
        googleMapApiKey: process.env.GOOGLE_MAP_API_KEY ?? '',
        packeteryApiKey: process.env.PACKETERY_API_KEY ?? '',
        cdnDomain: process.env.CDN_DOMAIN ?? '',
        sentryDsn,
        sentryEnvironment: process.env.SENTRY_ENVIRONMENT ?? '',
        sentryRelease: process.env.SENTRY_RELEASE ?? '',
        sentryFeedbackEnable: process.env.SENTRY_FEEDBACK_ENABLE === '1',
        sentryReplaysEnable: process.env.SENTRY_REPLAYS_ENABLE === '1',
        errorDebuggingLevel: process.env.ERROR_DEBUGGING_LEVEL ?? '',
        showSymfonyToolbar: process.env.SHOW_SYMFONY_TOOLBAR ?? '',
        shouldUseDefer: process.env.SHOULD_USE_DEFER === '1',
        userSnapApiKey: process.env.USERSNAP_PROJECT_API_KEY ?? '',
        userSnapEnabledDefaultValue: process.env.USERSNAP_STOREFRONT_ENABLED_BY_DEFAULT === '1',
        domains: [
            {
                publicGraphqlEndpoint: process.env.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_1 ?? '',
                url: domainUrl1,
                defaultLocale: 'en',
                currencyCode: 'EUR',
                fallbackTimezone: 'Europe/Prague',
                domainId: 1,
                mapSetting: { latitude: 49.8175, longitude: 15.473, zoom: 7 },
                gtmId: process.env.GTM_ID_1 ?? process.env.GTM_ID ?? '',
                isLuigisBoxActive: (process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS ?? '').split(',').includes('1'),
                packeteryCountry: 'cz',
                type: CustomerUserAreaEnum.B2C,
            },
            {
                publicGraphqlEndpoint: process.env.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_2 ?? '',
                url: domainUrl2,
                defaultLocale: 'cs',
                currencyCode: 'CZK',
                fallbackTimezone: 'Europe/Prague',
                domainId: 2,
                mapSetting: { latitude: 48.669, longitude: 19.699, zoom: 7 },
                gtmId: process.env.GTM_ID_2 ?? process.env.GTM_ID ?? '',
                isLuigisBoxActive: (process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS ?? '').split(',').includes('2'),
                packeteryCountry: 'cz',
                type: CustomerUserAreaEnum.B2B,
            },
            {
                publicGraphqlEndpoint: process.env.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_3 ?? '',
                url: domainUrl3,
                defaultLocale: 'sk',
                currencyCode: 'EUR',
                fallbackTimezone: 'Europe/Prague',
                domainId: 3,
                mapSetting: { latitude: 48.669, longitude: 19.699, zoom: 7 },
                gtmId: process.env.GTM_ID_3 ?? process.env.GTM_ID ?? '',
                isLuigisBoxActive: (process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS ?? '').split(',').includes('3'),
                packeteryCountry: 'sk',
                type: CustomerUserAreaEnum.B2B,
            },
        ],
    };
}
