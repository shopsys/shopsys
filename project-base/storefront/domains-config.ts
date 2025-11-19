import { Locale } from 'i18n-config';
import { CustomerUserAreaEnum } from 'types/customer';

export const cdnDomain = process.env.CDN_DOMAIN ?? '';

const domainsConfig = [
    {
        publicGraphqlEndpoint: process.env.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_1 ?? '',
        url: process.env.DOMAIN_HOSTNAME_1 ?? '',
        defaultLocale: 'en' as Locale,
        currencyCode: 'EUR',
        fallbackTimezone: 'Europe/Prague',
        domainId: 1,
        mapSetting: {
            latitude: 49.8175,
            longitude: 15.473,
            zoom: 7,
        },
        gtmId: process.env.GTM_ID,
        isLuigisBoxActive: (process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS ?? '').split(',').includes('1'),
        type: CustomerUserAreaEnum.B2C,
    },
    {
        publicGraphqlEndpoint: process.env.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_2 ?? '',
        url: process.env.DOMAIN_HOSTNAME_2 ?? '',
        defaultLocale: 'cs' as Locale,
        currencyCode: 'CZK',
        fallbackTimezone: 'Europe/Prague',
        domainId: 2,
        mapSetting: {
            latitude: 48.669,
            longitude: 19.699,
            zoom: 7,
        },
        gtmId: process.env.GTM_ID,
        isLuigisBoxActive: (process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS ?? '').split(',').includes('2'),
        type: CustomerUserAreaEnum.B2B,
    },
    {
        publicGraphqlEndpoint: process.env.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_3 ?? '',
        url: process.env.DOMAIN_HOSTNAME_3 ?? '',
        defaultLocale: 'sk' as Locale,
        currencyCode: 'EUR',
        fallbackTimezone: 'Europe/Prague',
        domainId: 3,
        mapSetting: {
            latitude: 48.669,
            longitude: 19.699,
            zoom: 7,
        },
        gtmId: process.env.GTM_ID,
        isLuigisBoxActive: (process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS ?? '').split(',').includes('3'),
        type: CustomerUserAreaEnum.B2B,
    },
];

export default domainsConfig;
