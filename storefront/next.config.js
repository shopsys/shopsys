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
    },
    eslint: {
        ignoreDuringBuilds: true,
    },
    images: {
        domains: i18n.domains.map((domainConfig) => {
            return domainConfig.backendHost;
        }),
    },
};
