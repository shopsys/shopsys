// eslint-disable-next-line @typescript-eslint/no-var-requires
const { i18n } = require('./next-i18next.config');

module.exports = {
    i18n,
    reactStrictMode: true,
    serverRuntimeConfig: {
        internalGraphqlEndpoint: process.env.INTERNAL_GRAPHQL_ENDPOINT,
    },
    publicRuntimeConfig: {
        publicGraphqlEndpoint: process.env.PUBLIC_GRAPHQL_ENDPOINT,
        domains: [
            {
                domain: process.env.DOMAIN_HOST_1,
                defaultLocale: 'cs',
                currencyCode: 'CZK',
            },
            {
                domain: process.env.DOMAIN_HOST_2,
                defaultLocale: 'sk',
                currencyCode: 'EUR',
            },
        ],
    },
    eslint: {
        ignoreDuringBuilds: true,
    },
    images: {
        domains: [process.env.DOMAIN_BACKEND_HOST_1, process.env.DOMAIN_BACKEND_HOST_2],
    },
};
