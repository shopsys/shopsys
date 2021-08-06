// eslint-disable-next-line @typescript-eslint/no-var-requires
const { i18n } = require('./next-i18next.config');

module.exports = {
    i18n,
    reactStrictMode: true,
    publicRuntimeConfig: {
        publicGraphqlEndpoint: process.env.PUBLIC_GRAPHQL_ENDPOINT,
    },
    eslint: {
        ignoreDuringBuilds: true,
    },
};
