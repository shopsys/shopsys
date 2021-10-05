// eslint-disable-next-line @typescript-eslint/no-var-requires
const { i18n } = require('./next-i18next.config');

module.exports = {
    i18n,
    reactStrictMode: true,
    serverRuntimeConfig: {
        internalGraphqlEndpoint: process.env.INTERNAL_GRAPHQL_ENDPOINT,
    },
    publicRuntimeConfig: {
        domains: [
            {
                publicGraphqlEndpoint: process.env.DOMAIN_HOSTNAME_1 + 'graphql/',
                url: process.env.DOMAIN_HOSTNAME_1,
                defaultLocale: 'cs',
                currencyCode: 'CZK',
            },
            {
                publicGraphqlEndpoint: process.env.DOMAIN_HOSTNAME_2 + 'graphql/',
                url: process.env.DOMAIN_HOSTNAME_2,
                defaultLocale: 'sk',
                currencyCode: 'EUR',
            },
        ],
        availableStaticUrls: {
            [process.env.DOMAIN_HOSTNAME_1]: {
                '/cart': '/kosik',
                '/order/shipment-and-payment': '/objednavka/doprava-a-platba',
                '/order/contact-information': '/objednavka/kontaktni-udaje',
                '/reset-password': '/zapomenute-heslo',
            },
            [process.env.DOMAIN_HOSTNAME_2]: {
                '/cart': '/kosik',
                '/order/shipment-and-payment': '/objednavka/doprava-a-platba',
                '/order/contact-information': '/objednavka/kontaktne-udaje',
                '/reset-password': '/zapomenute-heslo',
            },
        },
    },
    async rewrites() {
        return [
            // Czech URLs
            {
                source: '/kosik',
                destination: '/cart',
            },
            {
                source: '/objednavka/kontaktni-udaje',
                destination: '/order/contact-information',
            },
            {
                source: '/objednavka/doprava-a-platba',
                destination: '/order/shipment-and-payment',
            },
            {
                source: '/zapomenute-heslo',
                destination: '/reset-password',
            },
            // Slovak URLs
            {
                source: '/kosik',
                destination: '/cart',
            },
            {
                source: '/objednavka/kontaktne-udaje',
                destination: '/order/contact-information',
            },
            {
                source: '/objednavka/doprava-a-platba',
                destination: '/order/shipment-and-payment',
            },
            {
                source: '/zapomenute-heslo',
                destination: '/reset-password',
            },
        ];
    },
    eslint: {
        ignoreDuringBuilds: true,
    },
};
