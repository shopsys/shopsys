// eslint-disable-next-line @typescript-eslint/no-var-requires
const { i18n } = require('./next-i18next.config');
const { withSentryConfig } = require("@sentry/nextjs");

const moduleExports = {
    i18n,
    reactStrictMode: true,
    serverRuntimeConfig: {
        internalGraphqlEndpoint: process.env.INTERNAL_GRAPHQL_ENDPOINT,
    },
    publicRuntimeConfig: {
        googleMapApiKey: process.env.GOOGLE_MAP_API_KEY,
        sentryEnvironment: process.env.NEXT_PUBLIC_SENTRY_ENVIRONMENT || '',
        sentryDsn: process.env.NEXT_PUBLIC_SENTRY_DSN || '',
        domains: [
            {
                publicGraphqlEndpoint: process.env.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_1,
                url: process.env.DOMAIN_HOSTNAME_1,
                defaultLocale: 'cs',
                currencyCode: 'CZK',
                domainId: 1,
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
                domainId: 2,
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
                domainId: 1,
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
                '/brands-overview': '/prehled-znacek',
                '/login': '/prihlaseni',
                '/customer/orders': '/zakaznik/objednavky',
            },
            ['http://' + process.env.ACCEPTANCE_DOMAIN_HOST + '/']: {
                '/search': '/hledani',
                '/cart': '/kosik',
                '/order/transport-and-payment': '/objednavka/doprava-a-platba',
                '/order/contact-information': '/objednavka/kontaktni-udaje',
                '/reset-password': '/zapomenute-heslo',
                '/order-confirmation': '/potvrzeni-objednavky',
                '/stores': '/obchodni-domy',
                '/login': '/prihlaseni',
                '/customer/orders': '/zakaznik/objednavky',
            },
            [process.env.DOMAIN_HOSTNAME_2]: {
                '/search': '/hladanie',
                '/cart': '/kosik',
                '/order/transport-and-payment': '/objednavka/doprava-a-platba',
                '/order/contact-information': '/objednavka/kontaktne-udaje',
                '/reset-password': '/zapomenute-heslo',
                '/order-confirmation': '/potvrdenie-objednavky',
                '/stores': '/obchodne-domy',
                '/brands-overview': '/prehled-znacek',
                '/login': '/prihlasenie',
                '/customer/orders': '/zakaznik/objednavky',
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
                source: '/prihlaseni',
                destination: '/login',
            },
            {
                source: '/obchodni-domy',
                destination: '/stores',
            },
            {
                source: '/prehled-znacek',
                destination: '/brands-overview',
            },
            {
                source: '/zakaznik/objednavky',
                destination: '/customer/orders',
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
                source: '/prihlasenie',
                destination: '/login',
            },
            {
                source: '/obchodne-domy',
                destination: '/stores',
            },
            {
                source: '/prehled-znacek',
                destination: '/brands-overview',
            },
            {
                source: '/zakaznik/objednavky',
                destination: '/customer/orders',
            },
        ];
    },
    eslint: {
        ignoreDuringBuilds: true,
    },
};

const SentryWebpackPluginOptions = {};

module.exports = withSentryConfig(moduleExports, SentryWebpackPluginOptions);
