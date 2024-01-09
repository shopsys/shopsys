// eslint-disable-next-line @typescript-eslint/no-var-requires
const { withSentryConfig } = require('@sentry/nextjs');
// eslint-disable-next-line @typescript-eslint/no-var-requires
const nextTranslate = require('next-translate-plugin');
// eslint-disable-next-line @typescript-eslint/no-var-requires
const STATIC_REWRITE_PATHS = require('./config/staticRewritePaths');
// eslint-disable-next-line @typescript-eslint/no-var-requires
const withBundleAnalyzer = require('@next/bundle-analyzer')({
    enabled: process.env.ANALYZE === 'true',
});

/** @type {import('next').NextConfig} */
const nextConfig = {
    experimental: { scrollRestoration: true },
    reactStrictMode: true,
    swcMinify: true,
    assetPrefix: process.env.CDN_DOMAIN ?? undefined,
    sentry: {
        disableServerWebpackPlugin: process.env.APP_ENV === 'development',
        disableClientWebpackPlugin: process.env.APP_ENV === 'development',
        hideSourceMaps: true,
    },
    images: {
        loader: 'custom',
        remotePatterns: [
            {
                hostname: process.env.DOMAIN_HOSTNAME_1,
            },
            {
                hostname: process.env.DOMAIN_HOSTNAME_2,
            },
        ],
    },
    serverRuntimeConfig: {
        internalGraphqlEndpoint: `${process.env.INTERNAL_ENDPOINT}graphql/`,
    },
    publicRuntimeConfig: {
        googleMapApiKey: process.env.GOOGLE_MAP_API_KEY,
        packeteryApiKey: process.env.PACKETERY_API_KEY,
        lightgalleryLicenseKey: process.env.LIGHTGALLERY_LICENSE_KEY,
        cdnDomain: process.env.CDN_DOMAIN ?? '',
        sentryDsn: process.env.SENTRY_DSN ?? '',
        sentryEnvironment: process.env.SENTRY_ENVIRONMENT ?? '',
        errorDebugging: process.env.ERROR_DEBUGGING === '1',
        domains: [
            {
                publicGraphqlEndpoint: process.env.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_1,
                url: process.env.DOMAIN_HOSTNAME_1,
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
            },
            {
                publicGraphqlEndpoint: process.env.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_2,
                url: process.env.DOMAIN_HOSTNAME_2,
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
            },
        ],
        staticRewritePaths: STATIC_REWRITE_PATHS,
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
};

const SentryWebpackPluginOptions = {
    errorHandler: (err, invokeErr, compilation) => {
        compilation.warnings.push('Sentry CLI Plugin: ' + err.message);
    },
};

module.exports = withBundleAnalyzer(withSentryConfig(nextTranslate(nextConfig), SentryWebpackPluginOptions));
