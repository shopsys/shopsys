// eslint-disable-next-line @typescript-eslint/no-var-requires
const { i18n } = require('./next-i18next.config');
const { withSentryConfig } = require('@sentry/nextjs');

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
                '/registration': '/registrace',
                '/terms-and-conditions': '/obchodni-podminky',
                '/gdpr': '/zasady-ochrany-osobnich-udaju',
                '/new-password': '/nove-heslo',
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
                '/registration': '/registrace',
                '/terms-and-conditions': '/obchodni-podminky',
                '/gdpr': '/zasady-ochrany-osobnich-udaju',
                '/new-password': '/nove-heslo',
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
                '/registration': '/registracia',
                '/terms-and-conditions': '/podmienky-a-ustanovenia',
                '/gdpr': '/zasady-ochrany-osobnych-udajov',
                '/new-password': '/nove-heslo',
            },
        },
    },
    async rewrites() {
        const mappedRewrites = [];

        for (const domainHostName in this.publicRuntimeConfig.availableStaticUrls) {
            for (const key of Object.keys(this.publicRuntimeConfig.availableStaticUrls[domainHostName])) {
                mappedRewrites.push({
                    source: this.publicRuntimeConfig.availableStaticUrls[domainHostName][key],
                    destination: key,
                });
            }
        }

        return mappedRewrites;
    },
    eslint: {
        ignoreDuringBuilds: true,
    },
};

const SentryWebpackPluginOptions = {};

module.exports = withSentryConfig(moduleExports, SentryWebpackPluginOptions);
