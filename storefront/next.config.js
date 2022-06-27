// eslint-disable-next-line @typescript-eslint/no-var-requires
const { withSentryConfig } = require('@sentry/nextjs');
const nextTranslate = require('next-translate');

const staticUrls = {
    [process.env.DOMAIN_HOSTNAME_1]: {
        '/search': '/hledani',
        '/cart': '/kosik',
        '/contact': '/kontakt',
        '/order/transport-and-payment': '/objednavka/doprava-a-platba',
        '/order/contact-information': '/objednavka/kontaktni-udaje',
        '/reset-password': '/zapomenute-heslo',
        '/order-confirmation': '/potvrzeni-objednavky',
        '/stores': '/obchodni-domy',
        '/brands-overview': '/prehled-znacek',
        '/login': '/prihlaseni',
        '/customer': '/zakaznik',
        '/customer/edit-profile': '/zakaznik/upravit-udaje',
        '/customer/orders': '/zakaznik/objednavky',
        '/customer/order-detail': '/zakaznik/detail-objednavky',
        '/registration': '/registrace',
        '/new-password': '/nove-heslo',
        '/personal-data-overview': '/prehled-osobnich-udaju',
        '/personal-data-export': '/export-osobnich-udaju',
        '/order-payment-confirmation': '/potvrzeni-platby-objednavky',
        '/order/payment-status-notify': '/order/payment-status-notify',
        '/order-detail/:urlHash': '/detail-objednavky/:urlHash',
        '/cookie-consent': '/souhlas-se-soubory-cookies',
    },
    [process.env.DOMAIN_HOSTNAME_2]: {
        '/search': '/hladanie',
        '/cart': '/kosik',
        '/contact': '/kontakt',
        '/order/transport-and-payment': '/objednavka/doprava-a-platba',
        '/order/contact-information': '/objednavka/kontaktne-udaje',
        '/reset-password': '/zapomenute-heslo',
        '/order-confirmation': '/potvrdenie-objednavky',
        '/stores': '/obchodne-domy',
        '/brands-overview': '/prehled-znacek',
        '/login': '/prihlasenie',
        '/customer': '/zakaznik',
        '/customer/edit-profile': '/zakaznik/upravit-udaje',
        '/customer/orders': '/zakaznik/objednavky',
        '/customer/order-detail': '/zakaznik/detail-objednavky',
        '/registration': '/registracia',
        '/new-password': '/nove-heslo',
        '/personal-data-overview': '/prehlad-osobnych-udajov',
        '/personal-data-export': '/export-osobnych-udajov',
        '/order-payment-confirmation': '/potvrdenie-platby-objednavky',
        '/order/payment-status-notify': '/order/payment-status-notify',
        '/order-detail/:urlHash': '/detail-objednavky/:urlHash',
        '/cookie-consent': '/souhlas-se-soubory-cookies',
    },
};

// copy first domain as new third domain for acceptance (cypress) tests
staticUrls['http://' + process.env.ACCEPTANCE_DOMAIN_HOST + '/'] = staticUrls[process.env.DOMAIN_HOSTNAME_1];

const moduleExports = nextTranslate({
    reactStrictMode: true,
    sentry: {
        disableServerWebpackPlugin: process.env.NODE_ENV === 'development',
        disableClientWebpackPlugin: process.env.NODE_ENV === 'development',
    },
    serverRuntimeConfig: {
        internalGraphqlEndpoint: process.env.INTERNAL_GRAPHQL_ENDPOINT,
    },
    publicRuntimeConfig: {
        googleMapApiKey: process.env.GOOGLE_MAP_API_KEY,
        graphqlRedisCache: process.env.GRAPHQL_REDIS_CACHE,
        gtmId: process.env.GTM_ID,
        packeteryApiKey: process.env.PACKETERY_API_KEY,
        sentryDsn: process.env.SENTRY_DSN || '',
        sentryEnvironment: process.env.SENTRY_ENVIRONMENT || '',
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
        availableStaticUrls: staticUrls,
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
    // FE build error fix: "ModuleNotFoundError: Module not found: Error: Can't resolve 'net' in '/app/node_modules/@node-redis/client/dist/lib/client'"
    // https://github.com/webpack-contrib/css-loader/issues/447#issuecomment-761853289
    webpack: (config) => {
        config.resolve.fallback = {
            child_process: false,
            fs: false,
            util: false,
            http: false,
            https: false,
            tls: false,
            net: false,
            crypto: false,
            path: false,
            os: false,
            stream: false,
            zlib: false,
        };

        return config;
    },
});

const SentryWebpackPluginOptions = {};

module.exports = withSentryConfig(moduleExports, SentryWebpackPluginOptions);
