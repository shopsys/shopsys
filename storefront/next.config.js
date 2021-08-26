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
    },
    eslint: {
        ignoreDuringBuilds: true,
    },
};
