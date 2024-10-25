import { CustomerUserAreaEnum } from 'types/customer';

const domainsConfig = [
    {
        publicGraphqlEndpoint: process.env.NEXT_PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_1 ?? '',
        url: process.env.NEXT_PUBLIC_DOMAIN_HOSTNAME_1 ?? '',
        defaultLocale: 'en',
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
        publicGraphqlEndpoint: process.env.NEXT_PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_2 ?? '',
        url: process.env.NEXT_PUBLIC_DOMAIN_HOSTNAME_2 ?? '',
        defaultLocale: 'cs',
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
];

export default domainsConfig;
