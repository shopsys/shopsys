// eslint-disable-next-line @typescript-eslint/no-var-requires
const { i18n } = require('./next-i18next.config');

module.exports = {
    i18n,
    reactStrictMode: true,
    serverRuntimeConfig: {
        internalGraphqlEndpoint: process.env.INTERNAL_GRAPHQL_ENDPOINT,
    },
    publicRuntimeConfig: {
        googleMapApiKey: process.env.GOOGLE_MAP_API_KEY,
        domains: [
            {
                publicGraphqlEndpoint: process.env.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_1,
                url: process.env.DOMAIN_HOSTNAME_1,
                defaultLocale: 'cs',
                currencyCode: 'CZK',
                mapSetting: {
                    latitude: 49.8175,
                    longitude: 15.473,
                    zoom: 7,
                },
            },
            {
                publicGraphqlEndpoint: process.env.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_2,
                url: process.env.DOMAIN_HOSTNAME_2,
                defaultLocale: 'sk',
                currencyCode: 'EUR',
                mapSetting: {
                    latitude: 48.669,
                    longitude: 19.699,
                    zoom: 7,
                },
            },
            {
                publicGraphqlEndpoint: process.env.INTERNAL_GRAPHQL_ENDPOINT,
                url: 'http://' + process.env.ACCEPTANCE_DOMAIN_HOST + '/',
                defaultLocale: 'cs',
                currencyCode: 'CZK',
                mapSetting: {
                    latitude: 49.8175,
                    longitude: 15.473,
                    zoom: 7,
                },
            },
        ],
        availableStaticUrls: {
            [process.env.DOMAIN_HOSTNAME_1]: {
                '/search': '/hledani',
                '/cart': '/kosik',
                '/order/transport-and-payment': '/objednavka/doprava-a-platba',
                '/order/contact-information': '/objednavka/kontaktni-udaje',
                '/reset-password': '/zapomenute-heslo',
                '/order-confirmation': '/potvrzeni-objednavky',
                '/stores': '/obchodni-domy',
            },
            ['http://' + process.env.ACCEPTANCE_DOMAIN_HOST + '/']: {
                '/search': '/hledani',
                '/cart': '/kosik',
                '/order/transport-and-payment': '/objednavka/doprava-a-platba',
                '/order/contact-information': '/objednavka/kontaktni-udaje',
                '/reset-password': '/zapomenute-heslo',
                '/order-confirmation': '/potvrzeni-objednavky',
                '/stores': '/obchodni-domy',
            },
            [process.env.DOMAIN_HOSTNAME_2]: {
                '/search': '/hladanie',
                '/cart': '/kosik',
                '/order/transport-and-payment': '/objednavka/doprava-a-platba',
                '/order/contact-information': '/objednavka/kontaktne-udaje',
                '/reset-password': '/zapomenute-heslo',
                '/order-confirmation': '/potvrdenie-objednavky',
                '/stores': '/obchodne-domy',
            },
        },
    },
    async rewrites() {
        return [
            // Czech URLs
            {
                source: '/hledani',
                destination: '/search',
            },
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
                destination: '/order/transport-and-payment',
            },
            {
                source: '/zapomenute-heslo',
                destination: '/reset-password',
            },
            {
                source: '/potvrzeni-objednavky',
                destination: '/order-confirmation',
            },
            {
                source: '/obchodni-domy',
                destination: '/stores',
            },
            // Slovak URLs
            {
                source: '/hladanie',
                destination: '/search',
            },
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
                destination: '/order/transport-and-payment',
            },
            {
                source: '/zapomenute-heslo',
                destination: '/reset-password',
            },
            {
                source: '/potvrdenie-objednavky',
                destination: '/order-confirmation',
            },
            {
                source: '/obchodne-domy',
                destination: '/stores',
            },
        ];
    },
    eslint: {
        ignoreDuringBuilds: true,
    },
};
